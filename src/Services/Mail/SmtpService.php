<?php

declare(strict_types=1);

namespace DetailKing\Theme\Services\Mail;

use DetailKing\Theme\Core\Singleton;
use DetailKing\Theme\Core\ServiceInterface;
use PHPMailer\PHPMailer\PHPMailer;

defined('ABSPATH') || exit;

/**
 * SMTP delivery for wp_mail().
 *
 * Routes every email WordPress sends (including the custom-forms / leads
 * notifications from FormService) through a configured SMTP server, instead of
 * the host's unreliable PHP mail(). Settings live on their own top-level admin
 * menu ("SMTP", dashicons-email-alt) and are stored as a single option array
 * via the WordPress Settings API — deliberately NOT ACF, since mail delivery is
 * core infrastructure that must work whether or not ACF is installed.
 *
 * Configuration is applied on the `phpmailer_init` action. Sensitive values can
 * be overridden from wp-config.php with constants so they never live in the DB:
 *
 *   define('STACKPRESS_SMTP_HOST', 'smtp.example.com');
 *   define('STACKPRESS_SMTP_PORT', 587);
 *   define('STACKPRESS_SMTP_USER', 'postmaster@example.com');
 *   define('STACKPRESS_SMTP_PASS', '••••••••');          // strongly recommended
 *
 * A defined constant wins over the stored value and the field is shown
 * read-only in the admin.
 *
 * Filter the resolved settings with `detailking/theme/smtp/settings`.
 */
class SmtpService extends Singleton implements ServiceInterface
{
   /** Option key holding the settings array. */
   public const OPTION = 'detailking_smtp_settings';

   /** Settings group / page slug. */
   public const GROUP = 'detailking_smtp';
   public const MENU_SLUG = 'detailking-smtp';

   /** admin-post.php action for the "send test email" button. */
   private const TEST_ACTION = 'detailking_smtp_test';

   public function register(): void
   {
      add_action('admin_menu', [$this, 'registerMenu']);
      add_action('admin_init', [$this, 'registerSettings']);

      // Apply SMTP configuration to every outgoing message.
      add_action('phpmailer_init', [$this, 'configurePhpMailer']);

      // Optionally force the From identity site-wide.
      add_filter('wp_mail_from', [$this, 'filterFromEmail']);
      add_filter('wp_mail_from_name', [$this, 'filterFromName']);

      // Test-email handler (own nonce; never the Settings API one).
      add_action('admin_post_' . self::TEST_ACTION, [$this, 'handleTestEmail']);
   }

   // -------------------------------------------------------------------------
   // Settings storage
   // -------------------------------------------------------------------------

   /**
    * Default settings. Every stored value is merged over these, so missing keys
    * never throw.
    *
    * @return array<string,mixed>
    */
   private function defaults(): array
   {
      return [
         'enabled'    => false,
         'host'       => '',
         'port'       => 587,
         'encryption' => 'tls',   // tls | ssl | none
         'auth'       => true,
         'username'   => '',
         'password'   => '',
         'from_email' => '',
         'from_name'  => '',
         'force_from' => false,
      ];
   }

   /**
    * Resolved settings: stored values merged over defaults, with wp-config
    * constants overriding the sensitive connection fields.
    *
    * @return array<string,mixed>
    */
   public function settings(): array
   {
      $stored = get_option(self::OPTION, []);
      $stored = is_array($stored) ? $stored : [];

      $settings = array_merge($this->defaults(), $stored);

      if (defined('STACKPRESS_SMTP_HOST')) {
         $settings['host'] = (string) STACKPRESS_SMTP_HOST;
      }
      if (defined('STACKPRESS_SMTP_PORT')) {
         $settings['port'] = (int) STACKPRESS_SMTP_PORT;
      }
      if (defined('STACKPRESS_SMTP_USER')) {
         $settings['username'] = (string) STACKPRESS_SMTP_USER;
      }
      if (defined('STACKPRESS_SMTP_PASS')) {
         $settings['password'] = (string) STACKPRESS_SMTP_PASS;
      }

      /**
       * Filter the resolved SMTP settings.
       *
       * @param array<string,mixed> $settings
       */
      return (array) apply_filters('detailking/theme/smtp/settings', $settings);
   }

   /** Whether a given field is locked by a wp-config constant. */
   private function isConstant(string $field): bool
   {
      return match ($field) {
         'host'     => defined('STACKPRESS_SMTP_HOST'),
         'port'     => defined('STACKPRESS_SMTP_PORT'),
         'username' => defined('STACKPRESS_SMTP_USER'),
         'password' => defined('STACKPRESS_SMTP_PASS'),
         default    => false,
      };
   }

   public function registerSettings(): void
   {
      register_setting(self::GROUP, self::OPTION, [
         'type'              => 'array',
         'sanitize_callback' => [$this, 'sanitize'],
         'default'           => $this->defaults(),
      ]);
   }

   /**
    * Sanitise the submitted settings. The password is never wiped by an empty
    * submission (the field is rendered blank for security): when left empty the
    * previously stored password is retained.
    *
    * @param mixed $input
    * @return array<string,mixed>
    */
   public function sanitize($input): array
   {
      $input    = is_array($input) ? $input : [];
      $existing = get_option(self::OPTION, []);
      $existing = is_array($existing) ? $existing : [];
      $existing = array_merge($this->defaults(), $existing);

      $encryption = in_array($input['encryption'] ?? '', ['tls', 'ssl', 'none'], true)
         ? $input['encryption']
         : 'tls';

      $port = isset($input['port']) ? (int) $input['port'] : 587;
      if ($port < 1 || $port > 65535) {
         $port = 587;
      }

      $password = isset($input['password']) ? (string) $input['password'] : '';
      if ($password === '') {
         $password = (string) ($existing['password'] ?? '');
      }

      return [
         'enabled'    => !empty($input['enabled']),
         'host'       => sanitize_text_field((string) ($input['host'] ?? '')),
         'port'       => $port,
         'encryption' => $encryption,
         'auth'       => !empty($input['auth']),
         'username'   => sanitize_text_field((string) ($input['username'] ?? '')),
         'password'   => $password,
         'from_email' => sanitize_email((string) ($input['from_email'] ?? '')),
         'from_name'  => sanitize_text_field((string) ($input['from_name'] ?? '')),
         'force_from' => !empty($input['force_from']),
      ];
   }

   // -------------------------------------------------------------------------
   // PHPMailer configuration
   // -------------------------------------------------------------------------

   /**
    * Configure the PHPMailer instance to send over SMTP.
    *
    * @param PHPMailer $phpmailer Passed by reference by the phpmailer_init hook.
    */
   public function configurePhpMailer($phpmailer): void
   {
      $s = $this->settings();

      if (empty($s['enabled']) || $s['host'] === '') {
         return;
      }

      $phpmailer->isSMTP();
      $phpmailer->Host    = (string) $s['host'];
      $phpmailer->Port    = (int) $s['port'];
      $phpmailer->Timeout = 15;

      if ($s['encryption'] === 'none') {
         $phpmailer->SMTPSecure  = '';
         $phpmailer->SMTPAutoTLS = false;
      } else {
         // 'tls' => STARTTLS, 'ssl' => implicit TLS (SMTPS).
         $phpmailer->SMTPSecure = $s['encryption'] === 'ssl'
            ? PHPMailer::ENCRYPTION_SMTPS
            : PHPMailer::ENCRYPTION_STARTTLS;
      }

      if (!empty($s['auth'])) {
         $phpmailer->SMTPAuth = true;
         $phpmailer->Username = (string) $s['username'];
         $phpmailer->Password = (string) $s['password'];
      } else {
         $phpmailer->SMTPAuth = false;
      }

      if (!empty($s['from_email']) && is_email($s['from_email'])) {
         try {
            $phpmailer->setFrom((string) $s['from_email'], (string) $s['from_name'], false);
         } catch (\Throwable $e) {
            // Leave the existing From untouched if PHPMailer rejects it.
         }
      }
   }

   /** Force the From address when "force_from" is enabled. */
   public function filterFromEmail(string $from): string
   {
      $s = $this->settings();
      return (!empty($s['force_from']) && !empty($s['from_email']) && is_email($s['from_email']))
         ? (string) $s['from_email']
         : $from;
   }

   /** Force the From name when "force_from" is enabled. */
   public function filterFromName(string $name): string
   {
      $s = $this->settings();
      return (!empty($s['force_from']) && !empty($s['from_name']))
         ? (string) $s['from_name']
         : $name;
   }

   // -------------------------------------------------------------------------
   // Admin menu + page
   // -------------------------------------------------------------------------

   public function registerMenu(): void
   {
      add_menu_page(
         __('SMTP Settings', 'detailking'),
         __('SMTP', 'detailking'),
         'manage_options',
         self::MENU_SLUG,
         [$this, 'renderPage'],
         'dashicons-email-alt',
         80
      );
   }

   public function renderPage(): void
   {
      if (!current_user_can('manage_options')) {
         return;
      }

      $s = $this->settings();
      ?>
      <div class="wrap">
         <h1><?php esc_html_e('SMTP Settings', 'detailking'); ?></h1>
         <p class="description">
            <?php esc_html_e('Send all WordPress email (including form/lead notifications) through an SMTP server.', 'detailking'); ?>
         </p>

         <?php $this->renderNotices(); ?>

         <form method="post" action="options.php">
            <?php settings_fields(self::GROUP); ?>

            <table class="form-table" role="presentation">
               <tbody>
                  <tr>
                     <th scope="row"><?php esc_html_e('Enable SMTP', 'detailking'); ?></th>
                     <td>
                        <label>
                           <input type="checkbox" name="<?= esc_attr(self::OPTION); ?>[enabled]" value="1" <?php checked(!empty($s['enabled'])); ?>>
                           <?php esc_html_e('Route wp_mail() through the SMTP server below', 'detailking'); ?>
                        </label>
                     </td>
                  </tr>

                  <tr>
                     <th scope="row"><label for="sp-smtp-host"><?php esc_html_e('SMTP Host', 'detailking'); ?></label></th>
                     <td>
                        <input type="text" class="regular-text" id="sp-smtp-host"
                           name="<?= esc_attr(self::OPTION); ?>[host]"
                           value="<?= esc_attr((string) $s['host']); ?>"
                           placeholder="smtp.example.com"
                           <?php disabled($this->isConstant('host')); ?>>
                        <?php $this->constantHint('host', 'STACKPRESS_SMTP_HOST'); ?>
                     </td>
                  </tr>

                  <tr>
                     <th scope="row"><label for="sp-smtp-port"><?php esc_html_e('Port', 'detailking'); ?></label></th>
                     <td>
                        <input type="number" min="1" max="65535" class="small-text" id="sp-smtp-port"
                           name="<?= esc_attr(self::OPTION); ?>[port]"
                           value="<?= esc_attr((string) $s['port']); ?>"
                           <?php disabled($this->isConstant('port')); ?>>
                        <p class="description"><?php esc_html_e('Common: 587 (TLS), 465 (SSL), 25 (none).', 'detailking'); ?></p>
                        <?php $this->constantHint('port', 'STACKPRESS_SMTP_PORT'); ?>
                     </td>
                  </tr>

                  <tr>
                     <th scope="row"><?php esc_html_e('Encryption', 'detailking'); ?></th>
                     <td>
                        <fieldset>
                           <?php foreach (['tls' => 'TLS (STARTTLS)', 'ssl' => 'SSL', 'none' => __('None', 'detailking')] as $value => $label) : ?>
                              <label style="margin-right:1em;">
                                 <input type="radio" name="<?= esc_attr(self::OPTION); ?>[encryption]" value="<?= esc_attr($value); ?>" <?php checked($s['encryption'], $value); ?>>
                                 <?= esc_html($label); ?>
                              </label>
                           <?php endforeach; ?>
                        </fieldset>
                     </td>
                  </tr>

                  <tr>
                     <th scope="row"><?php esc_html_e('Authentication', 'detailking'); ?></th>
                     <td>
                        <label>
                           <input type="checkbox" name="<?= esc_attr(self::OPTION); ?>[auth]" value="1" <?php checked(!empty($s['auth'])); ?>>
                           <?php esc_html_e('Use SMTP username and password', 'detailking'); ?>
                        </label>
                     </td>
                  </tr>

                  <tr>
                     <th scope="row"><label for="sp-smtp-user"><?php esc_html_e('Username', 'detailking'); ?></label></th>
                     <td>
                        <input type="text" class="regular-text" id="sp-smtp-user"
                           name="<?= esc_attr(self::OPTION); ?>[username]"
                           value="<?= esc_attr((string) $s['username']); ?>"
                           autocomplete="off"
                           <?php disabled($this->isConstant('username')); ?>>
                        <?php $this->constantHint('username', 'STACKPRESS_SMTP_USER'); ?>
                     </td>
                  </tr>

                  <tr>
                     <th scope="row"><label for="sp-smtp-pass"><?php esc_html_e('Password', 'detailking'); ?></label></th>
                     <td>
                        <?php if ($this->isConstant('password')) : ?>
                           <input type="password" class="regular-text" value="********" disabled autocomplete="off">
                           <?php $this->constantHint('password', 'STACKPRESS_SMTP_PASS'); ?>
                        <?php else : ?>
                           <input type="password" class="regular-text" id="sp-smtp-pass"
                              name="<?= esc_attr(self::OPTION); ?>[password]"
                              value="" autocomplete="new-password"
                              placeholder="<?= !empty($s['password']) ? esc_attr__('•••••••• (unchanged)', 'detailking') : ''; ?>">
                           <p class="description">
                              <?php esc_html_e('Leave blank to keep the saved password. For best security, define STACKPRESS_SMTP_PASS in wp-config.php instead.', 'detailking'); ?>
                           </p>
                        <?php endif; ?>
                     </td>
                  </tr>

                  <tr>
                     <th scope="row"><label for="sp-smtp-from-email"><?php esc_html_e('From Email', 'detailking'); ?></label></th>
                     <td>
                        <input type="email" class="regular-text" id="sp-smtp-from-email"
                           name="<?= esc_attr(self::OPTION); ?>[from_email]"
                           value="<?= esc_attr((string) $s['from_email']); ?>"
                           placeholder="<?= esc_attr(get_option('admin_email')); ?>">
                     </td>
                  </tr>

                  <tr>
                     <th scope="row"><label for="sp-smtp-from-name"><?php esc_html_e('From Name', 'detailking'); ?></label></th>
                     <td>
                        <input type="text" class="regular-text" id="sp-smtp-from-name"
                           name="<?= esc_attr(self::OPTION); ?>[from_name]"
                           value="<?= esc_attr((string) $s['from_name']); ?>"
                           placeholder="<?= esc_attr(get_bloginfo('name')); ?>">
                     </td>
                  </tr>

                  <tr>
                     <th scope="row"><?php esc_html_e('Force From', 'detailking'); ?></th>
                     <td>
                        <label>
                           <input type="checkbox" name="<?= esc_attr(self::OPTION); ?>[force_from]" value="1" <?php checked(!empty($s['force_from'])); ?>>
                           <?php esc_html_e('Override the From address/name on every email (ignore values set by plugins)', 'detailking'); ?>
                        </label>
                     </td>
                  </tr>
               </tbody>
            </table>

            <?php submit_button(); ?>
         </form>

         <hr>

         <h2><?php esc_html_e('Send a Test Email', 'detailking'); ?></h2>
         <p class="description"><?php esc_html_e('Save your settings first, then send a test message to confirm delivery.', 'detailking'); ?></p>
         <form method="post" action="<?= esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="<?= esc_attr(self::TEST_ACTION); ?>">
            <?php wp_nonce_field(self::TEST_ACTION); ?>
            <input type="email" class="regular-text" name="test_email" required
               value="<?= esc_attr(wp_get_current_user()->user_email); ?>">
            <?php submit_button(__('Send Test Email', 'detailking'), 'secondary', 'submit', false); ?>
         </form>
      </div>
      <?php
   }

   /** Render an admin notice describing where a constant-locked value comes from. */
   private function constantHint(string $field, string $constant): void
   {
      if ($this->isConstant($field)) {
         printf(
            '<p class="description">%s <code>%s</code> %s</p>',
            esc_html__('Set by the', 'detailking'),
            esc_html($constant),
            esc_html__('constant in wp-config.php.', 'detailking')
         );
      }
   }

   /** Surface the test-email result passed back via query args. */
   private function renderNotices(): void
   {
      $sent = isset($_GET['sp_test']) ? sanitize_key((string) $_GET['sp_test']) : '';
      if ($sent === 'ok') {
         echo '<div class="notice notice-success is-dismissible"><p>'
            . esc_html__('Test email sent. Check the inbox.', 'detailking')
            . '</p></div>';
      } elseif ($sent === 'fail') {
         echo '<div class="notice notice-error is-dismissible"><p>'
            . esc_html__('Test email failed to send. Check your SMTP settings and the server mail log.', 'detailking')
            . '</p></div>';
      }
   }

   // -------------------------------------------------------------------------
   // Test email
   // -------------------------------------------------------------------------

   public function handleTestEmail(): void
   {
      if (!current_user_can('manage_options')) {
         wp_die(esc_html__('You are not allowed to do this.', 'detailking'));
      }

      check_admin_referer(self::TEST_ACTION);

      $to = sanitize_email((string) ($_POST['test_email'] ?? ''));
      $ok = false;

      if ($to !== '' && is_email($to)) {
         $ok = wp_mail(
            $to,
            sprintf(__('[%s] SMTP test email', 'detailking'), get_bloginfo('name')),
            __('This is a test email confirming your StackPress SMTP configuration works.', 'detailking')
         );
      }

      wp_safe_redirect(add_query_arg(
         'sp_test',
         $ok ? 'ok' : 'fail',
         admin_url('admin.php?page=' . self::MENU_SLUG)
      ));
      exit;
   }
}
