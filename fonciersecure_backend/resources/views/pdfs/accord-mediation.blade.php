<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Accord de Médiation</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12pt; line-height: 1.6; }
        h1 { text-align: center; font-size: 18pt; margin-bottom: 5px; }
        .sous-titre { text-align: center; font-size: 11pt; color: #555; margin-bottom: 30px; }
        .entete { margin-bottom: 30px; }
        .entete table { width: 100%; }
        .entete td { vertical-align: top; padding: 3px 10px; }
        .label { font-weight: bold; width: 150px; }
        .parties { margin-bottom: 30px; }
        .parties table { width: 100%; border-collapse: collapse; }
        .parties th, .parties td { border: 1px solid #333; padding: 8px; text-align: left; }
        .parties th { background: #eee; }
        .contenu { margin-bottom: 30px; text-align: justify; }
        .signatures { margin-top: 50px; }
        .signatures table { width: 100%; }
        .signatures td { text-align: center; padding-top: 60px; }
        .signature-line { border-top: 1px solid #333; width: 80%; margin: 0 auto; padding-top: 5px; }
        .footer { margin-top: 40px; font-size: 9pt; color: #888; text-align: center; }
    </style>
</head>
<body>
    <h1>ACCORD DE MÉDIATION</h1>
    <p class="sous-titre">Plateforme FoncierSecure Bénin</p>

    <div class="entete">
        <table>
            <tr><td class="label">N° dossier :</td><td>{{ $mediation->id }}</td></tr>
            <tr><td class="label">Titre :</td><td>{{ $mediation->titre }}</td></tr>
            <tr><td class="label">Date :</td><td>{{ now()->format('d/m/Y') }}</td></tr>
            <tr><td class="label">Médiateur :</td><td>{{ $mediation->mediateur?->prenom }} {{ $mediation->mediateur?->nom }}</td></tr>
        </table>
    </div>

    <div class="parties">
        <h3>Parties concernées</h3>
        <table>
            <tr><th>Partie</th><th>Rôle</th></tr>
            @foreach($mediation->parties as $partie)
                <tr><td>{{ $partie->user->prenom }} {{ $partie->user->nom }}</td><td>{{ $partie->role_partie }}</td></tr>
            @endforeach
        </table>
    </div>

    <div class="contenu">
        <h3>Solution convenue</h3>
        <p>{{ $mediation->solution }}</p>
    </div>

    @if($accord && $accord->contenu_accord)
    <div class="contenu">
        <h3>Contenu de l'accord</h3>
        <p>{{ $accord->contenu_accord }}</p>
    </div>
    @endif

    <div class="signatures">
        <table>
            <tr>
                @foreach($mediation->parties as $partie)
                    <td>
                        <div class="signature-line">{{ $partie->user->prenom }} {{ $partie->user->nom }}</div>
                    </td>
                @endforeach
            </tr>
        </table>
    </div>

    <div class="footer">
        Document généré par FoncierSecure Bénin le {{ now()->format('d/m/Y à H:i') }}
        - Ce document est la propriété des parties signataires
    </div>
</body>
</html>
