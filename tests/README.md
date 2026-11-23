# PHP tests

Run from the repository root with PHP 8.2 or later, SimpleXML, and DOM enabled.
Download the standalone schemas for the iTop 3.3 modules and the iTop 3.2 Datacenter View Extended bridge check.

```sh
mkdir -p .tmp/schemas/3.2 .tmp/schemas/3.3
curl --fail --location https://rudnerbjoern.github.io/iTop-schema/extension.xsd -o .tmp/schemas/extension.xsd
curl --fail --location https://rudnerbjoern.github.io/iTop-schema/3.2/itop_design.xsd -o .tmp/schemas/3.2/itop_design.xsd
curl --fail --location https://rudnerbjoern.github.io/iTop-schema/dist/3.3/itop_design.xsd -o .tmp/schemas/3.3/itop_design.xsd
```

PowerShell alternative:

```powershell
New-Item -ItemType Directory -Force .tmp/schemas/3.2, .tmp/schemas/3.3 | Out-Null
Invoke-WebRequest https://rudnerbjoern.github.io/iTop-schema/extension.xsd -OutFile .tmp/schemas/extension.xsd
Invoke-WebRequest https://rudnerbjoern.github.io/iTop-schema/3.2/itop_design.xsd -OutFile .tmp/schemas/3.2/itop_design.xsd
Invoke-WebRequest https://rudnerbjoern.github.io/iTop-schema/dist/3.3/itop_design.xsd -OutFile .tmp/schemas/3.3/itop_design.xsd
```

Alternatively, set `ITOP_SCHEMA_DIR` to a directory containing
`extension.xsd`, `3.3/itop_design.xsd`, and `3.2/itop_design.xsd`. Then run:

```sh
php tests/run.php
```

On Windows, use the same command with `php.exe` if needed.
No Composer packages, database, or iTop installation are required.
The runner prints each result and exits with `0` on success or `1` on failure.

For every PR targeting `main`, including PRs from forks, CI sets up PHP 8.3 with DOM and SimpleXML,
downloads the schemas into `.tmp/schemas`, and runs the same command.
The required `PR checks` job also requires this test job to succeed; a schema
download failure fails the job. Push-only runs continue to run Gitleaks only.
For `release/*` PRs, CI also validates the `release/X.Y.Z` branch name against
the manifest, the three supported module registrations, and internal dependencies, and includes that result in `PR checks`.

The tests execute `PDU::CreatePowerSockets` extracted from the local XML model,
using minimal iTop substitutes. They cover missing or exhausted capacity,
batch creation, name collisions, and repeated execution. Add cases to
`power-sockets.php`. Release preparation tests cover metadata updates and
invalid input, missing bridges, internal dependency alternatives, and preservation
of third-party requirements without writing project files.

The main module and its Datacenter View and TeemIP bridges use the iTop 3.3 XSD.
The Datacenter View Extended bridge uses the iTop 3.2 XSD.
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

The native tests exercise isolated PHP and XML behavior. The integration test below checks the installed iTop model.
Only run the test kit against trusted local project code: the extracted PHP
method is executed using `eval`.

## Installer and installed iTop tests

`installer.php` covers installer registration, fresh installation, repeat
setup, and upgrades before, across, and after the 2.0 migration threshold.

Install the extension in a disposable iTop 3.3 Provider/ITIL fixture and mount
the checkout at `/extension`. Use the fixture administrator password in
`ITOP_TEST_ADMIN_PASSWORD` or `ITOP_ADMIN_PASSWORD`. The test checks the compiled
layouts and verifies real A/B socket changes on servers and network devices.
It also runs the installer against legacy link fixtures to verify role
conversion, duplicate removal, and repeat-safe PDU link import.
Run it again in a fresh fixture with TeemIP Core 3.3.1 to check automatic
selection and PDU MAC and IP address persistence.

```sh
ITOP_TEST_MODE=1 php /extension/tests/integration.php
ITOP_TEST_MODE=1 php /extension/tests/integration.php --teemip
# In a fresh fixture with Molkobain Datacenter View 1.15.0
ITOP_TEST_MODE=1 php /extension/tests/integration.php --datacenter-view
```

The probe accepts only the dedicated test database
`test-db/itop_test/itop_test` or `db/itop_power_fixture/itop`, in host/schema/user
order. It restores the original role column definition and rolls back fixture data.
