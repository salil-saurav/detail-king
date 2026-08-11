<?php

declare(strict_types=1);

namespace DetailKing\Theme\Meta\Defaults;

defined('ABSPATH') || exit;

/**
 * A source of default content for one field group.
 *
 * The point of this contract is that a default is declared *once* and consumed
 * in three places:
 *
 *   1. the ACF field's `default_value`, so a fresh page arrives pre-filled;
 *   2. the template's fallback, so the page renders correctly even with ACF
 *      deactivated or the field emptied;
 *   3. the repeater seeder, which cannot use `default_value` because ACF does
 *      not support defaults on repeater rows.
 *
 * Declaring it once is what prevents the whole "empty ACF field behind an
 * `if ($field)` guard silently deletes the element" bug family — there is no
 * empty state to guard against, because every field has a value.
 *
 * Drop a class implementing this into src/Meta/Defaults/ and DefaultsRegistry
 * discovers it. No registration step.
 */
interface DefaultsProvider
{
   /**
    * Flat map of field name => default value.
    *
    * Use the field's real name, unprefixed. Values may be strings, numbers,
    * booleans, or arrays (for repeaters — one entry per row, each row an
    * associative array of sub-field name => value).
    *
    * @return array<string,mixed>
    */
   public function defaults(): array;
}
