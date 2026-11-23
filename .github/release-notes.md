## iTop compatibility

Power Infrastructure 3.3.x requires iTop 3.3.0 or later.
The optional Datacenter View bridge groups the PDU rack fields for Molkobain Datacenter View 1.15.0 or later.
The optional TeemIP bridge supports TeemIP Core 3.3.1 and uses its Datacenter
Management adapter.

## 📦 Installation

1. Download the `br-power-infrastructure-vX.Y.Z.zip` asset attached to this release.
2. Extract its contents into your iTop installation's `extensions/` directory,
   preserving the included folder structure. The package creates a single
   `extensions/br-power-infrastructure/` folder containing the manifest, main module, and optional Datacenter View and TeemIP bridges.
3. Run iTop setup and select **Datamodel: Power Infrastructure** to activate the extension.

### Verify your download

Download the matching `.zip.sha256` file alongside the ZIP. From the folder
containing both files, verify the package before extracting it:

```sh
sha256sum --check br-power-infrastructure-vX.Y.Z.zip.sha256
```

On Windows, use `Get-FileHash .\br-power-infrastructure-vX.Y.Z.zip -Algorithm SHA256`
and compare the result with the hash in the `.sha256` file.

