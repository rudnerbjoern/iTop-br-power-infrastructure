# PHP tests

Run from the repository root with PHP 8.2 or later, SimpleXML, and DOM enabled.
First download the two standalone schemas used by the project's XML files:

```sh
mkdir -p .tmp/schemas/3.2
curl --fail --location https://rudnerbjoern.github.io/iTop-schema/extension.xsd -o .tmp/schemas/extension.xsd
curl --fail --location https://rudnerbjoern.github.io/iTop-schema/3.2/itop_design.xsd -o .tmp/schemas/3.2/itop_design.xsd
```

PowerShell alternative:

```powershell
New-Item -ItemType Directory -Force .tmp/schemas/3.2 | Out-Null
Invoke-WebRequest https://rudnerbjoern.github.io/iTop-schema/extension.xsd -OutFile .tmp/schemas/extension.xsd
Invoke-WebRequest https://rudnerbjoern.github.io/iTop-schema/3.2/itop_design.xsd -OutFile .tmp/schemas/3.2/itop_design.xsd
```

Alternatively, set `ITOP_SCHEMA_DIR` to an existing iTop-schema checkout containing
`extension.xsd` and `3.2/itop_design.xsd`. Then run:

```sh
php tests/run.php
```

On Windows, use the same command with `php.exe` if needed.
No Composer packages, database, or iTop installation are required.
The runner prints each result and exits with `0` on success or `1` on failure.

For every PR targeting `main`, including PRs from forks, CI sets up PHP 8.3 with DOM and SimpleXML,
downloads both schemas into `.tmp/schemas`, and runs the same command.
The required `PR checks` job also requires this test job to succeed; a schema
download failure fails the job. Push-only runs continue to run Gitleaks only.
For `release/*` PRs, CI also validates the `release/X.Y.Z` branch name against
the manifest, all four module registrations, and internal dependencies, and includes that result in `PR checks`.

The tests execute `PDU::CreatePowerSockets` extracted from the local XML model,
using minimal iTop substitutes. They cover missing or exhausted capacity,
batch creation, name collisions, and repeated execution. Add cases to
`power-sockets.php`. Release preparation tests cover metadata updates and
invalid input, missing bridges, internal dependency alternatives, and preservation
of third-party requirements without writing project files.

Additional tests validate the manifest and all four module data models against the local XSDs.
Missing schemas fail the tests with setup instructions. Tests run offline after
setup; refresh the cached schemas explicitly when needed. Results apply to the
schema versions present locally.

`socket-assignment.php` loads the real `_PowerSocket` implementation and checks
first-free A/B assignment, matching PDU references, repeated connection,
disconnection, full-device rejection and rollback, and socket-type rejection.
The substitutes implement only the required iTop object operations.

Fork PR tests require no repository secrets or write token. GitHub repository
or organization settings may require maintainer approval before a fork workflow
starts. The workflow uses `pull_request`, not `pull_request_target`, for tests.

These tests do not test iTop event dispatch, attribute flags, persistence, or installation.
Only run the test kit against trusted local project code: the extracted PHP
method is executed using `eval`.
