# Managing RC Runtime Fix Wave 46

Status: runtime-fix wave after owner-side proof.

## Fixed

- Fixed lazy runtime publication handler construction after the publication field accessor split.
- Fixed RC architecture guard path normalization on Windows so generated CRUD bridge controllers remain excluded.
- Fixed canon guard naming rule for `AbstractManage*` symbols.
- Fixed PowerShell compatibility proof to avoid writing to the same log that capture script is teeing.
- Fixed runtime intake classification for UTF-16/PowerShell logs by normalizing log text before matching.
- Fixed label humanization double spaces for snake_case fields.
- Fixed host CRUD discovery to scan nested component `Entity` folders.
- Adjusted EasyAdmin page configurator unit test to avoid version-specific private DTO accessor.
- Adjusted entity surface test expectation for merged audit/publication date fields.

## Runtime status

This wave is expected to reduce the current owner-side proof failures. Runtime green still requires a new local `capture-rc-runtime-proof.ps1` run and assertion.
