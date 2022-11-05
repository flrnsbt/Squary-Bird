<?php
include_once "settings.php";
include_once 'utils.php';

header('Content-type: text/xml');
header('Cache-Control: no-cache');
header('Cache-Control: no-store', false);

$dom = new DOMDocument();
$dom->encoding = 'utf-8';
$dom->xmlVersion = '1.0';
$dom->formatOutput = true;
$root = $dom->createElement("result");
$dom->appendChild($root);

if (isset($_POST['query_type'])) {
    $type = sanitizeString($_POST['query_type']);
    $columns = sanitizeString($_POST['columns']);
    try {
        $query = "SELECT " . $columns . " FROM " . $type . " " .  match ($type) {
            "city" => "WHERE province_name = ?",
            "province" => "WHERE country_id = ?",
            "users" => "WHERE user_id = ?",
            "user_score" => "r JOIN users r2 ON r.user_id = r2.user_id ORDER BY score DESC LIMIT ?",
            "country" => "WHERE 1"
        };
        $stmt = db()->prepare($query);
        if(isset($_POST['args'])){
            $args = sanitizeString($_POST['args']);
            $stmt->bind_param(str_repeat('s', count($args)), ...$args);

        }
        $stmt->execute();
        $results = $stmt->get_result();
        if ($results->num_rows !== 0) {
            foreach ($results as $row) {
                $node = $dom->createElement($type);
                foreach ($row as $key => $value) {
                    $node->setAttributeNode(new DOMAttr($key, $value));
                }
                $root->appendChild($node);
            }
        }
    } catch (Exception $e) {
        $error = $dom->createElement('error');
        $root->appendChild($error);
        $err_msg = new DOMAttr('message', $query);
        $error->setAttributeNode($err_msg);
    }
}

echo $dom->saveXML();
