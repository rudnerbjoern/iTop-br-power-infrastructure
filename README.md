# iTop-br-power-infrastructure

Copyright (c) 2026 Björn Rudner
[![License](https://img.shields.io/github/license/rudnerbjoern/iTop-br-power-infrastructure)](https://github.com/rudnerbjoern/iTop-br-power-infrastructure/blob/main/LICENSE)

## What?

`iTop-br-power-infrastructure` is an extension for iTop that enhances the native CMDB data model for documenting and managing power infrastructure components.

The extension focuses on structured modeling of electrical power supply components and their relationships in environments such as data centers, server rooms, technical facilities, and other infrastructure areas where reliable documentation of power dependencies is required.

It extends the native iTop power model and introduces additional classes and attributes for documenting technical, operational, and maintenance-related information.

## Features

- Extends the native iTop classes related to power infrastructure
- Adds generic electrical and maintenance-related attributes to existing power classes
- Introduces a dedicated `UPS` class derived from `PowerSource`
- Introduces a dedicated `UPSBattery` class derived from `PhysicalDevice`
- Supports relationships between UPS systems and their battery units
- Improves documentation of electrical characteristics such as phase type, nominal voltage, nominal frequency, and maximum current
- Adds UPS-specific documentation fields such as topology, rated power, and autonomy time
- Adds battery-specific documentation fields such as battery role, battery type, battery status, replacement dates, voltage, and capacity
- Provides a structured foundation for future extensions covering additional power infrastructure components such as generators

## Data Model

The extension currently builds on the following native iTop classes:

- `PowerConnection`
- `PowerSource`
- `PDU`

The extension currently introduces the following additional classes:

- `UPS`
- `UPSBattery`

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

Presentation enhancements for inherited generic power-related attributes.

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

## Relations

The current data model supports relationships such as:

- `PowerSource` feeding `PDU`
- `UPS` acting as a specialized `PowerSource`
- `UPS` owning one or more `UPSBattery` objects
- chained power infrastructure dependencies through the native iTop power model

## Installation

1. Clone or copy this extension into your iTop `extensions` directory:

   `extensions/iTop-br-power-infrastructure`

2. Make sure the extension files are placed in the correct module directory structure.

3. Run the iTop setup or upgrade process.

## Status

This extension is currently in an early beta stage.

The current implementation focuses on:

- extending the generic native power infrastructure model
- introducing a dedicated UPS class
- introducing a dedicated UPS battery class
- establishing relations between UPS systems and their battery units
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
