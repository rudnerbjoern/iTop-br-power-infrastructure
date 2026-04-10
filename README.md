# iTop-br-power-infrastructure

Copyright (c) 2026 Björn Rudner
[![License](https://img.shields.io/github/license/rudnerbjoern/iTop-br-power-infrastructure)](https://github.com/rudnerbjoern/iTop-br-power-infrastructure/blob/main/LICENSE)

## What?

`iTop-br-power-infrastructure` is an extension for iTop that enhances the native CMDB data model for documenting and managing power infrastructure components.

The extension focuses on structured modeling of electrical power supply components and their relationships in environments such as data centers, server rooms, technical facilities, and other infrastructure areas where reliable documentation of power dependencies is required.

It extends the native iTop power model and introduces additional classes, attributes, and synchronization logic for documenting technical, operational, and maintenance-related information.

## Features

- Extends the native iTop classes related to power infrastructure
- Adds generic electrical and maintenance-related attributes to existing power classes
- Introduces a dedicated `UPS` class derived from `PowerSource`
- Introduces a dedicated `UPSBattery` class derived from `PhysicalDevice`
- Introduces a dedicated `PowerGenerator` class derived from `PowerSource`
- Supports relationships between UPS systems and their battery units
- Improves documentation of electrical characteristics such as phase type, nominal voltage, nominal frequency, and maximum current
- Adds UPS-specific documentation fields such as topology, rated power, and autonomy time
- Adds battery-specific documentation fields such as battery role, battery type, battery status, replacement dates, voltage, and capacity
- Introduces the `PowerSocket` class to model individual PDU outlets
- Extends `PDU` with dedicated power socket handling
- Allows `DatacenterDevice` objects to be connected to specific PDU sockets
- Provides automatic synchronization logic between `PowerSocket`, `PDU`, and `DatacenterDevice`
- Supports automatic slot assignment for Power A / Power B connections
- Includes consistency checks and rollback logic for invalid assignments
- Provides generator-specific documentation fields such as generator type, fuel type (including diesel), tank capacity, runtime, and test schedule

## Data Model

The extension currently builds on the following native iTop classes:

- `PowerConnection`
- `PowerSource`
- `PDU`
- `DatacenterDevice`

The extension currently introduces the following additional classes:

- `UPS`
- `UPSBattery`
- `PowerGenerator`
- `PowerSocket`

### Extended native classes

#### `PowerConnection`

Additional generic attributes for electrical and maintenance-related documentation:

- `phase_type`
- `nominal_voltage`
- `nominal_frequency`
- `max_current_ampere`
- `last_maintenance_date`
- `next_maintenance_date`

#### `PowerSource`

Presentation enhancements for inherited generic power-related attributes.

#### `PDU`

Presentation enhancements for inherited generic power-related attributes and support for individual power sockets.

#### `DatacenterDevice`

Extended to support dedicated Power A / Power B socket assignments.

### New classes

#### `UPS`

A dedicated UPS class based on `PowerSource`.

Current UPS-specific attributes include:

- `ups_topology`
- `rated_power_va`
- `rated_power_watt`
- `autonomy_time`

In addition, the class provides:

- `batteries_list`
- inherited relationships to downstream PDUs

#### `UPSBattery`

A dedicated battery unit class based on `PhysicalDevice`.

Current battery-specific attributes include:

- `ups_id`
- `battery_role`
- `battery_type`
- `battery_status`
- `battery_voltage`
- `battery_capacity_ah`
- `last_replacement_date`
- `next_replacement_date`

#### `PowerGenerator`

A dedicated generator class based on `PowerSource`.

Current generator-specific attributes include:

- `generator_type`
- `fuel_type`
- `rated_power_kva`
- `fuel_tank_capacity_l`
- `fuel_consumption_lph`
- `autonomy_time`
- `start_method`
- `is_ats_available`
- `last_test_date`
- `next_test_date`

In addition, the class provides:

- inherited relationships to downstream PDUs

#### `PowerSocket`

A `PowerSocket` represents an individual physical outlet on a PDU.

Each socket:

- belongs to exactly one `PDU`
- may be connected to exactly one `DatacenterDevice`
- can be assigned to either Power A or Power B on the connected device

## Relations

The current data model supports relationships such as:

- `PowerSource` feeding `PDU`
- `UPS` acting as a specialized `PowerSource`
- `UPS` owning one or more `UPSBattery` objects
- `PowerGenerator` acting as a specialized `PowerSource`
- `PDU` owning one or more `PowerSocket` objects
- `PowerSocket` being linked to a `DatacenterDevice`
- chained power infrastructure dependencies through the native iTop power model

## PowerSocket Logic

The integrated PowerSocket functionality provides synchronization logic between:

- `PowerSocket`
- `PDU`
- `DatacenterDevice`

Each `DatacenterDevice` can be connected to:

- one Power A socket
- one Power B socket

The extension ensures that:

- no more than two sockets are assigned to a single `DatacenterDevice`
- slots are automatically assigned (A first, then B)
- connections stay consistent when objects are updated or deleted
- invalid assignments are rejected or rolled back

## How to Use

### UPS and UPSBattery

Use the `UPS` class to document dedicated uninterruptible power supplies and the `UPSBattery` class to document associated battery units.

Typical usage includes:

- documenting UPS-specific electrical and technical data
- assigning one or more battery units to a UPS
- documenting replacement cycles and battery status

### PowerGenerator

Use the `PowerGenerator` class to document diesel generators and other common emergency power units (gensets).

Typical usage includes:

- documenting generator category (standby, prime, continuous, mobile)
- tracking fuel type (for example diesel, gas, HVO, biodiesel, dual-fuel)
- recording rated power, fuel tank capacity, and fuel consumption
- planning generator test runs using last/next test dates

### PowerSockets

#### Creating PowerSockets

PowerSockets represent physical outlets on a PDU.

1. Open the PDU object in iTop.
2. Scroll to the PowerSockets list.
3. Add one or more PowerSockets.
4. Give each socket a meaningful name, for example `Outlet 1`, `A01`, or `Rack-3-Port-5`.

Each `PowerSocket` belongs to exactly one `PDU`.

#### Connecting a PowerSocket to a DatacenterDevice

You can connect a `PowerSocket` to a `DatacenterDevice` in two ways.

##### Option A: From the PowerSocket side

1. Open a `PowerSocket`.
2. Set the `Datacenter Device` field.
3. Save.

The system will automatically:

- assign the socket to slot Power A or Power B
- set the corresponding PDU reference on the device

##### Option B: From the DatacenterDevice side

1. Open a `DatacenterDevice` such as a server, storage system, or switch.
2. Select a `PowerSocket` in:
   - `Power A socket`, or
   - `Power B socket`
3. Save.

The system will automatically:

- link the `PowerSocket` back to the `DatacenterDevice`
- keep both sides consistent

#### Automatic Slot Assignment

Each `DatacenterDevice` can have:

- one Power A socket
- one Power B socket

When connecting a `PowerSocket`:

1. Slot A is used first
2. Slot B is used if A is already occupied
3. If both slots are occupied, the assignment is rejected

#### Deleting a PowerSocket

When a `PowerSocket` is deleted:

- the slot reference on the `DatacenterDevice` is automatically cleared
- Power A / Power B fields are reset if they pointed to this socket

This prevents broken references and dangling assignments.

## Installation

1. Clone or copy this extension into your iTop `extensions` directory:

   `extensions/iTop-br-power-infrastructure`

2. Make sure the extension files are placed in the correct module directory structure.

3. Run the iTop setup or upgrade process.

4. Apply the data model changes and complete the update.

## Status

This extension is currently in an early beta stage.

The current implementation focuses on:

- extending the generic native power infrastructure model
- introducing a dedicated UPS class
- introducing a dedicated UPS battery class
- introducing a dedicated generator class
- introducing PowerSocket support for PDUs
- establishing relations between UPS systems and their battery units
- providing synchronized socket handling for `DatacenterDevice`
- preparing the module structure for future power infrastructure extensions

## iTop Compatibility

The extension was tested on:

- iTop `3.2.2`

Required dependency:

- `itop-datacenter-mgmt/3.2.1`

## Roadmap

Planned or possible future enhancements may include:

- additional UPS-related technical attributes
- support for generator-related classes
- more detailed power source modeling
- improved monitoring and operational metadata
- further refinement of power infrastructure relations
- additional power distribution and socket-level modeling improvements

## Screenshots

### Power Supply

![Power Supply](doc/Screenshots/PowerSupply.png)

### PDU

![PDU](doc/Screenshots/PDU.png)

## Attribution

This extension uses icons from:

![power connector](br-power-infrastructure/images/powersocket.png) by Arthur Shlain from <https://thenounproject.com/browse/icons/term/power-connector/>
