= Managing RC-clean wave 16

Base: `Managing_rc_clean_wave15_cumulative.zip`.

== Goal

Reduce duplicated low-level policy normalization logic and keep policy classes focused on behavior instead of array coercion/parsing.

== Changes

* Added `App\Managing\Service\Policy\ManagePolicyValueNormalizer`.
* Moved shared normalization concerns into the policy helper:
** string list trimming/filtering/deduplication;
** lowercase keyword list normalization;
** normalized string maps;
** integer maps.
* Updated `ManageCrudResourcePolicy` to delegate policy-value coercion to the shared normalizer.
* Updated `ManageCrudFieldPolicy` to use the shared normalizer for title/identity/keyword candidates.
* Updated `ManageCrudBehaviorPolicy` to use the shared normalizer for CRUD surface candidate lists.
* Added `tests/Unit/Policy/ManagePolicyValueNormalizerTest.php`.

== Result

* `ManageCrudResourcePolicy` reduced from about 263 lines to about 166 lines.
* `ManageCrudBehaviorPolicy` reduced from about 132 lines to about 113 lines.
* Policy classes now carry less duplicated defensive parsing code.
* Component-specific policy remains config-driven through `Configuration`/`ManagingExtension`.

== Validation

* `php -l` passed for all PHP files under `src`, `tests`, and `tools`.
* PHPUnit/PHPStan were not executed because this archive does not include runtime `vendor/`.
