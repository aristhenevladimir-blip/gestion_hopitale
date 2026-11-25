<?php
function patient_valid_date($value) {
    if (!$value) return false;
    if (!preg_match('/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/', (string) $value, $m)) return false;
    return checkdate((int) $m[2], (int) $m[3], (int) $m[1]);
}

function patient_text_length($value) {
    return function_exists("mb_strlen") ? mb_strlen($value, "UTF-8") : strlen($value);
}

function patient_validation_errors($nom, $prenom, $sexe, $recordDate, $tel = "", $adresse = "", $email = "") {
    $errors = [];
    if (!is_string($nom) || !is_string($prenom)) $errors[] = "Le nom et le prénom sont invalides.";
    elseif ($nom === "" || $prenom === "") $errors[] = "Le nom et le prénom sont obligatoires.";
    elseif (patient_text_length($nom) > 50 || patient_text_length($prenom) > 50)
        $errors[] = "Le nom et le prénom ne peuvent pas dépasser 50 caractères.";
    if (!in_array($sexe, ["Masculin", "Féminin"], true)) $errors[] = "Le sexe est obligatoire.";
    if (!is_string($recordDate) || !patient_valid_date($recordDate)) $errors[] = "La date d'enregistrement est invalide.";
    if (!is_string($tel) || patient_text_length($tel) > 20) $errors[] = "Le téléphone ne peut pas dépasser 20 caractères.";
    if (!is_string($adresse) || patient_text_length($adresse) > 100) $errors[] = "L'adresse ne peut pas dépasser 100 caractères.";
    if (!is_string($email) || patient_text_length($email) > 50 || ($email !== "" && filter_var($email, FILTER_VALIDATE_EMAIL) === false))
        $errors[] = "L'adresse e-mail est invalide ou trop longue.";
    return $errors;
}
