<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Facture {{ $facture->reference }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #2D6A4F; margin: 0; }
        .infos { margin-bottom: 20px; }
        .infos table { width: 100%; }
        .infos td { vertical-align: top; padding: 4px; }
        .details { width: 100%; border-collapse: collapse; margin: 20px 0; }
        .details th { background: #2D6A4F; color: white; padding: 8px; text-align: left; }
        .details td { padding: 8px; border-bottom: 1px solid #ddd; }
        .total { text-align: right; font-size: 16px; font-weight: bold; margin-top: 20px; }
        .footer { margin-top: 40px; text-align: center; font-size: 10px; color: #666; }
        .badge { display: inline-block; padding: 4px 12px; border-radius: 4px; font-size: 11px; }
        .badge-brouillon { background: #f0ad4e; color: white; }
        .badge-envoyee { background: #5bc0de; color: white; }
        .badge-payee { background: #5cb85c; color: white; }
    </style>
</head>
<body>
    <div class="header">
        <h1>FACTURE</h1>
        <p>Réf: {{ $facture->reference }}</p>
        <span class="badge badge-{{ $facture->statut }}">{{ ucfirst($facture->statut) }}</span>
    </div>

    <div class="infos">
        <table>
            <tr>
                <td><strong>Émetteur:</strong><br>{{ $facture->emetteur->prenom }} {{ $facture->emetteur->nom }}<br>{{ $facture->emetteur->email }}</td>
                <td><strong>Date:</strong><br>{{ $facture->created_at->format('d/m/Y') }}</td>
            </tr>
            <tr>
                <td><strong>Dossier:</strong><br>{{ $facture->dossier->titre }}</td>
                <td><strong>Parcelle:</strong><br>{{ $facture->dossier->parcelle->code ?? 'N/A' }}</td>
            </tr>
        </table>
    </div>

    <table class="details">
        <thead>
            <tr>
                <th>Description</th>
                <th style="text-align:right">Montant (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $facture->description ?? 'Prestations notariales' }}</td>
                <td style="text-align:right">{{ number_format($facture->montant, 0, ',', ' ') }}</td>
            </tr>
            <tr>
                <td><strong>Total TTC</strong></td>
                <td style="text-align:right"><strong>{{ number_format($facture->montant, 0, ',', ' ') }} FCFA</strong></td>
            </tr>
        </tbody>
    </table>

    <div class="footer">
        <p>FoncierSecure — Document généré le {{ now()->format('d/m/Y à H:i') }}</p>
    </div>
</body>
</html>
