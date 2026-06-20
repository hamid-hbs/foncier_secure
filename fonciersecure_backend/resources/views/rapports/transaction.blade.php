<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Dossier de Transaction</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        h1 { color: #1a5276; border-bottom: 2px solid #1a5276; padding-bottom: 5px; }
        h2 { color: #2e86c1; margin-top: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #1a5276; color: white; }
        .statut { padding: 3px 8px; border-radius: 3px; font-weight: bold; }
        .message { background: #f9f9f9; padding: 8px; margin: 5px 0; border-left: 3px solid #2e86c1; }
        .footer { margin-top: 30px; font-size: 10px; color: #777; text-align: center; }
    </style>
</head>
<body>
    <h1>Dossier de Transaction Foncière</h1>
    <p><strong>N° :</strong> DT-{{ $dossier->id }} | <strong>Date :</strong> {{ $dossier->created_at->format('d/m/Y') }}</p>

    <h2>Informations générales</h2>
    <table>
        <tr><th>Titre</th><td>{{ $dossier->titre }}</td></tr>
        <tr><th>Statut</th><td>{{ $dossier->statut }}</td></tr>
        <tr><th>Vendeur</th><td>{{ $dossier->vendeur?->prenom }} {{ $dossier->vendeur?->nom }}</td></tr>
        <tr><th>Acheteur</th><td>{{ $dossier->acheteur?->prenom ?? 'Non défini' }} {{ $dossier->acheteur?->nom ?? '' }}</td></tr>
        <tr><th>Date de clôture</th><td>{{ $dossier->closed_at?->format('d/m/Y') ?? 'Non clôturé' }}</td></tr>
    </table>

    <h2>Intervenants</h2>
    <table>
        <tr><th>Nom</th><th>Rôle</th><th>Accepté le</th></tr>
        @foreach($dossier->intervenants as $inv)
            <tr>
                <td>{{ $inv->user?->prenom }} {{ $inv->user?->nom }}</td>
                <td>{{ $inv->role_dossier }}</td>
                <td>{{ $inv->accepted_at?->format('d/m/Y') ?? 'En attente' }}</td>
            </tr>
        @endforeach
    </table>

    <h2>Documents</h2>
    <table>
        <tr><th>Fichier</th><th>Déposé par</th><th>Version</th></tr>
        @foreach($dossier->documents as $doc)
            <tr>
                <td>{{ $doc->nom_fichier }}</td>
                <td>{{ $doc->uploader?->prenom }} {{ $doc->uploader?->nom }}</td>
                <td>{{ $doc->version }}</td>
            </tr>
        @endforeach
    </table>

    <h2>Fil d'activité</h2>
    @foreach($dossier->activites as $act)
        <div class="message">
            <strong>{{ $act->created_at->format('d/m/Y H:i') }}</strong> —
            {{ $act->user?->prenom }} {{ $act->user?->nom }} :
            {{ $act->action }}
        </div>
    @endforeach

    <div class="footer">
        Document généré par FoncierSecure Bénin — {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
