# Prepare a release

Set up the local schemas as described in [the test instructions](../tests/README.md),
then run from the repository root:

```sh
php scripts/prepare-release.php 3.2.4
```

This updates the extension manifest version, all four module registration versions, internal dependency versions, and
manifest release date together, then runs `tests/run.php`. The default date uses
Europe/Berlin. Supply a date explicitly when needed:

```sh
php scripts/prepare-release.php 3.2.4 2026-11-06
```

The version must be newer than the existing version and use `X.Y.Z` format.
The current manifest and main module versions must match. The target must be
newer than every module version, including the bridges. All inputs and all five file
contents are checked before writing. A write failure triggers an attempt to
restore all originals; this is not a transaction across filesystem failures.
Existing formatting outside the release metadata values is preserved.

If tests fail, the command exits unsuccessfully and retains the version edits
for inspection. Resolve failures before publishing. The script does not stage,
commit, push, tag, or publish anything.

Use a release branch named `release/<version>`, for example `release/3.2.4`,
for this preparation. Review the diff, commit on that branch, and open a PR
into `main`. PR CI requires the branch version to match the manifest, all four registrations, and internal dependencies.

Merging a `release/X.Y.Z` PR from this same repository automatically starts the
`Release` workflow. The merge is the publication approval: the workflow verifies
the successful `PR checks` result and publishes the merge commit as tag `vX.Y.Z`,
ZIP package, and GitHub Release. Closing an unmerged PR, merging another branch
prefix, or merging a fork's PR does not publish a release.

The ZIP contains a single top-level `br-power-infrastructure/` folder. Inside it,
`extension.xml`, `LICENSE`, the `br-power-infrastructure/` module folder, and all three optional bridge
module folders retain their
project-relative structure. Extracting into iTop's `extensions/` directory keeps
all package files under `extensions/br-power-infrastructure/`.

Each release includes `br-power-infrastructure-vX.Y.Z.zip.sha256` alongside its ZIP.
Download both into the same directory and run
`sha256sum --check br-power-infrastructure-vX.Y.Z.zip.sha256` to verify the package.
On recovery, already uploaded assets must match the regenerated files before
the workflow adds a missing asset or publishes an interrupted draft.

The `Release` workflow can still be started manually on `main` for recovery.
Retry the original run to retain its exact release commit if `main` has advanced.
Existing tags are reused only when they point to the same commit. Missing or
incomplete uploads and interrupted drafts can be completed by a repeat run.

The helper updates the manifest version and release date, all four module
registrations, and all internal `br-power-infrastructure` dependencies, including
`||` alternatives. Third-party dependency versions and historical installer
migration thresholds remain unchanged. Bridges currently use `3.2.3`, so the
first unified release must be newer, for example `3.2.4`.
