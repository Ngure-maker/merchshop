<?php
require_once __DIR__ . '/db.php';

function getSetting(string $key, $default = null) {
    try {
        $db = new DBHelper();
        $setting = $db->fetchOne("SELECT setting_value FROM site_settings WHERE setting_key = ?", [$key]);
        if ($setting && array_key_exists('setting_value', $setting)) {
            return $setting['setting_value'];
        }
    } catch (Exception $e) {
        // ignore
    }
    return $default;
}

function setSetting(string $key, $value): bool {
    try {
        $db = new DBHelper();
        $existing = $db->fetchOne("SELECT id FROM site_settings WHERE setting_key = ?", [$key]);
        if ($existing) {
            return (bool)$db->update('site_settings', ['setting_value' => (string)$value], "setting_key = '" . addslashes($key) . "'");
        }
        return (bool)$db->insert('site_settings', [
            'setting_key' => $key,
            'setting_value' => (string)$value
        ]);
    } catch (Exception $e) {
        return false;
    }
}
?>
