<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Alerte fraude détectée</title>
</head>
<body>
    <h1>Alerte fraude détectée</h1>

    <p><strong>Niveau :</strong> {{ $alerte->niveau->label() }}</p>
    <p><strong>Règle déclenchée :</strong> {{ $alerte->regle_declenchee }}</p>
    <p><strong>Paiement concerné :</strong> #{{ $alerte->payement_id }}</p>
    <p><strong>Détectée le :</strong> {{ $alerte->created_at }}</p>
</body>
</html>
