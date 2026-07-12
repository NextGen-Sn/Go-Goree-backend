<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Rapport journalier Go Gorée</title>
</head>
<body>
    <h1>Rapport journalier Go Gorée — {{ $donnees['date'] ?? now()->format('Y-m-d') }}</h1>

    <h2>Ventes</h2>
    @forelse($donnees['ventes'] ?? [] as $vente)
        <p>{{ $vente }}</p>
    @empty
        <p>Aucune donnée.</p>
    @endforelse

    <h2>Gains</h2>
    @forelse($donnees['gains'] ?? [] as $gain)
        <p>{{ $gain }}</p>
    @empty
        <p>Aucune donnée.</p>
    @endforelse

    <h2>Fraudes</h2>
    @forelse($donnees['fraudes'] ?? [] as $fraude)
        <p>{{ $fraude }}</p>
    @empty
        <p>Aucune donnée.</p>
    @endforelse
</body>
</html>
