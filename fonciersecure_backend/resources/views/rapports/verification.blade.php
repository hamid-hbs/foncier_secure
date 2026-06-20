<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rapport de Vérification</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { color: #1a5276; border-bottom: 2px solid #1a5276; padding-bottom: 5px; }
        h2 { color: #2e86c1; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #1a5276; color: white; }
        .faible { color: #27ae60; font-weight: bold; }
        .moyen { color: #f39c12; font-weight: bold; }
        .eleve { color: #e74c3c; font-weight: bold; }
        .footer { margin-top: 30px; font-size: 10px; color: #777; text-align: center; }
        .score { font-size: 24px; text-align: center; padding: 20px; }
    </style>
</head>
<body>
    <h1>Rapport de Vérification Foncière</h1>
    <p><strong>N° :</strong> VF-{{ $verification->id }} | <strong>Date :</strong> {{ $verification->created_at->format('d/m/Y') }}</p>

    <h2>Informations du terrain</h2>
    <table>
        <tr><th>Titre</th><td>{{ $verification->titre }}</td></tr>
        <tr><th>Commune</th><td>{{ $verification->commune?->nom ?? 'Non spécifiée' }}</td></tr>
        <tr><th>Superficie déclarée</th><td>{{ number_format($verification->superficie_declaree ?? 0, 2) }} m²</td></tr>
        <tr><th>Coordonnées GPS</th><td>{{ $verification->latitude }}, {{ $verification->longitude }}</td></tr>
    </table>

    <h2>Score de risque</h2>
    <div class="score {{ $verification->niveau_risque }}">
        {{ $verification->score_risque }}/100 — {{ ucfirst($verification->niveau_risque) }}
    </div>

    <h2>Documents soumis</h2>
    <table>
        <tr><th>Type</th><th>Nom</th><th>Hash SHA-256</th></tr>
        @foreach($verification->documents as $doc)
            <tr>
                <td>{{ strtoupper($doc->type_document) }}</td>
                <td>{{ $doc->nom_fichier }}</td>
                <td>{{ $doc->hash_sha256 }}</td>
            </tr>
        @endforeach
    </table>

    <h2>Analyses automatiques</h2>
    <table>
        <tr><th>Analyse</th><th>Résultat</th><th>Détails</th></tr>
        @foreach($verification->analyses as $analyse)
            <tr>
                <td>{{ ucfirst($analyse->type_analyse) }}</td>
                <td>{{ $analyse->resultat }}</td>
                <td>{{ json_encode($analyse->details) }}</td>
            </tr>
        @endforeach
    </table>

    @if($verification->intervention)
        <h2>Intervention du géomètre</h2>
        <table>
            <tr><th>Statut</th><td>{{ $verification->intervention->statut }}</td></tr>
            <tr><th>Avis</th><td>{{ $verification->intervention->avis ?? 'En attente' }}</td></tr>
            <tr><th>Commentaire</th><td>{{ $verification->intervention->commentaire ?? '-' }}</td></tr>
        </table>
    @endif

    <div class="footer">
        Document généré par FoncierSecure Bénin — {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
