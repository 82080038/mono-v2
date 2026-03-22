<?php
// Simple step validation runner
require_once 'api/step-by-step-validation.php';

$validation = new StepByStepValidation();
$results = $validation->runStepByStepValidation();
?>
