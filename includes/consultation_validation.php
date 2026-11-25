<?php
require_once __DIR__ . "/patient_schema.php";

function consultation_convert_measurements($values) {
    $weightKg = null;
    if (isset($values["poids"]) && $values["poids"] !== "" && is_numeric($values["poids"])) {
        $weightKg = (float) $values["poids"];
        $u = $values["poids_unit"] ?? "kg";
        if ($u === "g") $weightKg /= 1000;
        elseif ($u === "lb") $weightKg *= 0.453592;
    }
    $heightCm = null;
    if (isset($values["taille"]) && $values["taille"] !== "" && is_numeric($values["taille"])) {
        $heightCm = (float) $values["taille"];
        if (($values["taille_unit"] ?? "cm") === "m") $heightCm *= 100;
    }
    return ["poids_kg" => $weightKg, "taille_cm" => $heightCm];
}

function consultation_validation_errors($values, $weightKg, $heightCm) {
    $errors = [];
    if (!isset($values["id_patient"]) || !is_numeric($values["id_patient"]) || (int) $values["id_patient"] <= 0)
        $errors[] = "Veuillez sélectionner un patient.";
    if (!isset($values["medecin"]) || !is_string($values["medecin"]) || trim($values["medecin"]) === "")
        $errors[] = "Le nom du médecin est obligatoire.";
    elseif (patient_text_length($values["medecin"]) > 100)
        $errors[] = "Le nom du médecin ne peut pas dépasser 100 caractères.";
    if (!isset($values["diagnostique"]) || !is_string($values["diagnostique"]) || trim($values["diagnostique"]) === "")
        $errors[] = "Le diagnostic est obligatoire.";
    $date = $values["date_consultation"] ?? null;
    if (!is_string($date) || !patient_valid_date($date)) $errors[] = "La date de consultation est invalide.";
    elseif ($date > date("Y-m-d")) $errors[] = "La date de consultation ne peut pas être dans le futur.";
    $weight = $values["poids"] ?? "";
    $weightUnit = $values["poids_unit"] ?? "kg";
    if (!is_string($weight) || ($weight !== "" && !is_numeric($weight)) || !in_array($weightUnit, ["kg","g","lb"], true))
        $errors[] = "Le poids ou son unité est invalide.";
    $height = $values["taille"] ?? "";
    $heightUnit = $values["taille_unit"] ?? "cm";
    if (!is_string($height) || ($height !== "" && !is_numeric($height)) || !in_array($heightUnit, ["cm","m"], true))
        $errors[] = "La taille ou son unité est invalide.";
    $telephone = $values["telephone"] ?? "";
    if (!is_string($telephone) || patient_text_length($telephone) > 15)
        $errors[] = "Le téléphone du médecin ne peut pas dépasser 15 caractères.";
    if ($weightKg !== null && ($weightKg < 1 || $weightKg > 400)) $errors[] = "Le poids doit être compris entre 1 et 400 kg.";
    if ($heightCm !== null && ($heightCm < 30 || $heightCm > 260)) $errors[] = "La taille doit être comprise entre 30 et 260 cm.";
    return $errors;
}

function consultation_fields($values, $poidsKg, $tailleCm) {
    return [
        "Id_Patient" => $values["id_patient"], "Nom_Prenom_medecin" => $values["medecin"],
        "Telephone" => $values["telephone"], "Poids" => $poidsKg, "Taille_cm" => $tailleCm,
        "Diagnostique" => $values["diagnostique"], "Date_Consultation" => $values["date_consultation"],
    ];
}
