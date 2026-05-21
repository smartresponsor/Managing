# Managing runtime-fix wave 48

Base: `Managing_rc_clean_wave47_cumulative.zip`

## Purpose

Fix the remaining PHPUnit failures reported after wave47. The runtime proof had syntax, canon guard, Composer validation, and PowerShell compatibility passing, while PHPUnit still failed in path normalization and host CRUD resource discovery.

## Changes

- Fixed `ManageFilesystemPathNormalizer::isAbsolutePath()` Windows drive detection by using a valid escaped regular expression.
- Preserved Windows absolute paths such as `C:\\work\\app\\src` instead of prefixing the project directory.
- Treated one-segment project-root paths such as `/src` as project-relative roots when resolving against a project dir.
- Kept multi-segment Unix absolute paths such as `/var/app/src` absolute.
- This should unblock host Doctrine mapping resolution because `%kernel.project_dir%/...` mapping dirs are no longer accidentally re-prefixed.

## Runtime status

Local syntax check for touched PHP files passed in the assistant environment. Full PHPUnit/PHPStan/runtime proof must be run in the owner Windows environment with installed dependencies.
