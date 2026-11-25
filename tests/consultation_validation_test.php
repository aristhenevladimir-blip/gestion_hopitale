<?php
require_once __DIR__ . "/../includes/consultation_validation.php";

$values = [
    "id_patient" => 1,
    "medecin" => "Dr Test",
    "diagnostique" => "Suivi",
    "date_consultation" => date("Y-m-d"),
];

if (consultation_validation_errors($values, null, null) !== []) {
    throw new RuntimeException("Valid consultation values were rejected.");
}

$values["date_consultation"] = date("Y-m-d", strtotime("+1 day"));
$errors = consultation_validation_errors($values, null, null);
if (
    !in_array(
        "La date de consultation ne peut pas être dans le futur.",
        $errors,
        true,
    )
) {
    throw new RuntimeException("Future consultation dates were not rejected.");
}

$values["date_consultation"] = "2026-02-31";
$errors = consultation_validation_errors($values, null, null);
if (!in_array("La date de consultation est invalide.", $errors, true)) {
    throw new RuntimeException(
        "Impossible consultation dates were not rejected.",
    );
}
$values["date_consultation"] = date("Y-m-d");

$errors = consultation_validation_errors($values, 401, 29);
if (
    !in_array("Le poids doit être compris entre 1 et 400 kg.", $errors, true) ||
    !in_array("La taille doit être comprise entre 30 et 260 cm.", $errors, true)
) {
    throw new RuntimeException("Weight or height limits were not enforced.");
}

$values["date_consultation"] = date("Y-m-d");
$values["poids"] = "2.2";
$values["poids_unit"] = "lb";
$values["taille"] = "1.75";
$values["taille_unit"] = "m";
$measurements = consultation_convert_measurements($values);
if (
    abs($measurements["poids_kg"] - 0.9979024) > 0.000001 ||
    $measurements["taille_cm"] !== 175.0
) {
    throw new RuntimeException("Consultation measurements were not converted.");
}

$values["poids"] = "";
$values["taille"] = "invalid";
$measurements = consultation_convert_measurements($values);
if ($measurements["poids_kg"] !== null || $measurements["taille_cm"] !== null) {
    throw new RuntimeException(
        "Empty or invalid measurements were not ignored.",
    );
}
$errors = consultation_validation_errors(
    $values,
    $measurements["poids_kg"],
    $measurements["taille_cm"],
);
if (!in_array("La taille ou son unité est invalide.", $errors, true)) {
    throw new RuntimeException(
        "Invalid measurement input was silently accepted.",
    );
}
$values["taille"] = "";
$values["poids_unit"] = "stone";
$errors = consultation_validation_errors($values, null, null);
if (!in_array("Le poids ou son unité est invalide.", $errors, true)) {
    throw new RuntimeException("Unknown measurement units were accepted.");
}

$valuesWithRemovedColumns = [
    "id_patient" => 1,
    "medecin" => "Dr Test",
    "telephone" => "",
    "diagnostique" => "Suivi",
    "date_consultation" => date("Y-m-d"),
    "motif" => "",
    "temperature" => "37.2",
    "tension" => "120/80",
    "freq_cardiaque" => "72",
    "freq_respiratoire" => "16",
    "spo2" => "98",
    "glycemie" => "100",
    "examen_clinique" => "Examen conserve",
    "examens_complementaires" => "",
    "statut" => "terminee",
    "prochain_rdv" => "2030-01-01",
    "heure_rdv" => "09:30",
    "recommandations" => "",
    "notes_suivi" => "Note conservee",
];
$fields = consultation_fields($valuesWithRemovedColumns, 70, 175);
if (
    array_keys($fields) !== [
        "Id_Patient",
        "Nom_Prenom_medecin",
        "Telephone",
        "Poids",
        "Taille_cm",
        "Diagnostique",
        "Date_Consultation",
    ] ||
    $fields["Poids"] !== 70 ||
    $fields["Taille_cm"] !== 175
) {
    throw new RuntimeException(
        "Consultation fields do not match the project schema.",
    );
}

echo "consultation_validation_test: OK\n";
