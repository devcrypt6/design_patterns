<?php
/**
 * Class Adapter (simple version)
 *
 * Goal: Use XML data with code that expects a simple ConfigReader.
 * We build an adapter that reads XML and offers a clean get() method.
 *
 * Steps:
 * 1) Adaptee: a class that gives us a SimpleXMLElement from a raw XML string.
 * 2) Adapter: extends the Adaptee and implements ConfigReader. It converts
 *    dot paths like "app.db.port" into XPath and returns a PHP value.
 */

/**
 * Adaptee / SDK
 *
 * Knows how to load and expose the XML.
 */
class XmlConfig
{
    public function __construct(private readonly string $xmlString) {}

    public function xml(): \SimpleXMLElement
    {
        return new \SimpleXMLElement($this->xmlString);
    }
}

/**
 * Adapter
 *
 * Reuses XmlConfig (extends it) and implements the interface the client wants
 * (ConfigReader). Turns "dot paths" into XPath and returns the value.
 */
final class XmlConfigAdapter extends XmlConfig implements ConfigReader
{
    public function get(string $key, mixed $default = null): mixed
    {
        // Turn "a.b.c" into an XPath like "//a/b/c"
        $xpath = '//'.str_replace('.', '/', $key);
        $nodes = $this->xml()->xpath($xpath);

        if (!$nodes || !isset($nodes[0])) {
            return $default;
        }

        $value = (string)$nodes[0];

        // Helpful casting: numbers and booleans
        if (is_numeric($value)) return $value + 0;
        $lower = strtolower($value);
        if ($lower === 'true') return true;
        if ($lower === 'false') return false;

        return $value;
    }
}

/**
 * What the client code expects
 */
interface ConfigReader
{
    public function get(string $key, mixed $default = null): mixed;
}

// Usage
$xml = <<<XML
<app>
  <db>
    <host>localhost</host>
    <port>5432</port>
    <ssl>true</ssl>
  </db>
</app>
XML;

$cfg = new XmlConfigAdapter($xml);
echo $cfg->get('app.db.port');  // Output: 5432
