<?php
require_once __DIR__ . "/../includes/patient_schema.php";

$errors = patient_validation_errors("Nom", "Prenom", "Masculin", date("Y-m-d"));
if ($errors !== []) {
    throw new RuntimeException("Valid patient values were rejected.");
}

$errors = patient_validation_errors("", "", "Autre", "invalid");
if (
    count($errors) !== 3 ||
    !in_array("Le nom et le prénom sont obligatoires.", $errors, true) ||
    !in_array("Le sexe est obligatoire.", $errors, true) ||
    !in_array("La date d'enregistrement est invalide.", $errors, true)
) {
    throw new RuntimeException("Invalid patient values were not rejected.");
}

if (patient_valid_date("2026-02-31") || patient_valid_date("2026-02-28junk")) {
    throw new RuntimeException("Invalid patient dates were accepted.");
}
$errors = patient_validation_errors(
    str_repeat("N", 51),
    "Prenom",
    "Masculin",
    date("Y-m-d"),
    "",
    "",
    "not-an-email",
);
if (
    !in_array(
        "Le nom et le prénom ne peuvent pas dépasser 50 caractères.",
        $errors,
        true,
    ) ||
    !in_array("L'adresse e-mail est invalide ou trop longue.", $errors, true)
) {
    throw new RuntimeException(
        "Patient field length or email validation failed.",
    );
}

echo "patient_validation_test: OK\n";
