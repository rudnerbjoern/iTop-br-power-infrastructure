<?php

declare(strict_types=1);

$schemaDirectory = getenv('ITOP_SCHEMA_DIR') ?: __DIR__ . '/../.tmp/schemas';
$documents = [
    'extension manifest matches its XSD' => ['extension.xml', 'extension.xsd'],
    'power infrastructure data model matches the iTop 3.3 XSD' => [
        'br-power-infrastructure/datamodel.br-power-infrastructure.xml', '3.3/itop_design.xsd',
    ],
    'Datacenter View bridge matches the iTop 3.3 XSD' => [
        'br-power-infrastructure-bridge-for-datacenter-view/datamodel.br-power-infrastructure-bridge-for-datacenter-view.xml',
        '3.3/itop_design.xsd',
    ],
    'Datacenter View Extended bridge matches the iTop 3.2 XSD' => [
        'br-power-infrastructure-bridge-for-datacenter-view-extended/datamodel.br-power-infrastructure-bridge-for-datacenter-view-extended.xml',
        '3.2/itop_design.xsd',
    ],
];

$documents['TeemIP bridge matches the iTop 3.3 XSD'] = [
    'br-power-infrastructure-bridge-for-teemip-ip-mgmt/datamodel.br-power-infrastructure-bridge-for-teemip-ip-mgmt.xml',
    '3.3/itop_design.xsd',
];

$tests = [];
foreach ($documents as $name => [$documentPath, $schemaPath]) {
    $tests[$name] = static function () use ($schemaDirectory, $documentPath, $schemaPath): void {
        $schema = $schemaDirectory . '/' . $schemaPath;
        if (!is_file($schema)) {
            throw new RuntimeException("Missing schema: $schema. See tests/README.md for setup.");
        }
        $previous = libxml_use_internal_errors(true);
        libxml_clear_errors();
        try {
            $document = new DOMDocument();
            if (!$document->load(__DIR__ . '/../' . $documentPath, LIBXML_NONET)
                || !$document->schemaValidate($schema, LIBXML_NONET)) {
                $messages = array_map(
                    static fn (LibXMLError $error): string =>
                        'Line ' . $error->line . ': ' . trim($error->message),
                    libxml_get_errors()
                );
                throw new RuntimeException(implode('; ', $messages));
            }
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
    };
}

return $tests;
