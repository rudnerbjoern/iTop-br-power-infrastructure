# iTop-br-power-infrastructure

Copyright (c) 2026 Björn Rudner
[![License](https://img.shields.io/github/license/rudnerbjoern/iTop-br-power-infrastructure)](https://github.com/rudnerbjoern/iTop-br-power-infrastructure/blob/main/LICENSE)

## What?

`iTop-br-power-infrastructure` is an extension for iTop that enhances the CMDB data model to better document and manage power infrastructure components.

The goal of this extension is to provide a solid foundation for modeling electrical power supply structures and dependencies in the CMDB, including components such as power sources, UPS systems, PDUs, and related infrastructure.

The extension is designed to support data centers, server rooms, technical facilities, and other environments where structured documentation of power infrastructure is required.

## Features

- Extends the native iTop power infrastructure model
- Enhances existing power-related classes with additional documentation fields
- Provides a foundation for modeling UPS systems as dedicated configuration items
- Supports structured documentation of electrical characteristics, operational data, maintenance information, and monitoring details
- Helps represent dependencies between power infrastructure components and downstream devices
- Designed to remain generic enough for multiple types of power infrastructure, including UPS systems, generators, and other power sources

## Relations

The extension is intended to build on the native iTop classes related to power infrastructure, especially:

- `PowerConnection`
- `PowerSource`
- `PDU`

Depending on the implemented version, the extension may introduce additional classes such as:

- `UPS`
- further power-related infrastructure classes in later versions

Typical relationships supported by the data model include:

- power sources feeding PDUs
- UPS systems acting as specialized power sources
- chained power infrastructure components
- dependencies between power infrastructure and protected devices

## Installation

1. Clone or copy this extension into your iTop `extensions` directory:
   - `extensions/iTop-br-power-infrastructure`

2. Run the iTop setup/upgrade process.

3. Apply the data model changes and complete the update.

## Status

This extension is currently in an early stage of development.

The initial focus is on:

- evaluating the native iTop power model
- extending generic power-related base classes where appropriate
- preparing the data model for dedicated UPS support
- defining a consistent structure for future power infrastructure extensions

## iTop Compatibility

The extension was tested on iTop 3.2.2

## Attribution

This Extension uses Icons from:

- [Icons8](https://icons8.com)
