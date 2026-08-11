<?php

/**
 * Google "G" mark, in Google's own brand colours.
 *
 * Kept as a separate part because it appears twice (the summary card and every
 * review card) and because it is the one mark on the page that must NOT inherit
 * currentColor — recolouring a third party's logo gold would misrepresent it.
 *
 * @package DetailKing Theme
 */

if (!defined('ABSPATH')) exit;
?>
<svg class="dk-google" viewBox="0 0 24 24" width="20" height="20" focusable="false" aria-hidden="true">
   <path fill="#4285F4" d="M21.6 12.2c0-.7-.1-1.3-.2-1.9H12v3.6h5.4a4.7 4.7 0 0 1-2 3.1v2.6h3.2c1.9-1.7 3-4.3 3-7.4z"/>
   <path fill="#34A853" d="M12 22c2.7 0 4.9-.9 6.6-2.4l-3.2-2.6c-.9.6-2 1-3.4 1a5.9 5.9 0 0 1-5.6-4.1H3.1v2.7A10 10 0 0 0 12 22z"/>
   <path fill="#FBBC05" d="M6.4 13.9a6 6 0 0 1 0-3.8V7.4H3.1a10 10 0 0 0 0 9l3.3-2.5z"/>
   <path fill="#EA4335" d="M12 5.9c1.5 0 2.8.5 3.8 1.5l2.8-2.8A10 10 0 0 0 3.1 7.4l3.3 2.7A5.9 5.9 0 0 1 12 5.9z"/>
</svg>
