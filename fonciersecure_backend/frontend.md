# Frontend FoncierSecure v8.0 — Guide API, Models & Workflow

## Palette "Green Tree"

| Token | Hex |
|-------|-----|
| `--green-tree` | `#2D6A4F` |
| `--green-tree-light` | `#40916C` |
| `--green-tree-dark` | `#1B4332` |
| `--gold` | `#D4A373` |
| `--danger` | `#D62828` |
| `--warning` | `#E76F51` |
| `--info` | `#457B9D` |
| `--bg-page` | `#F0F7F4` |
| `--surface` | `#FFFFFF` |
| `--text-primary` | `#1B1B1B` |
| `--text-secondary` | `#6B7280` |
| `--border` | `#E5E7EB` |

Icons: Font Awesome 6 Free (`fas`, `far`, `fab`).

---

## 1. Structure de la base de données

### 1.1 Tables et relations

#### auth
```
users
  id, nom, prenom, email, telephone, password, otp, otp_expires_at,
  email_verified_at, is_active, role (enum: citoyen|geometre|notaire|admin),
  indice_confiance (int, defaut 0), deleted_at (soft), timestamps

  relations:
    hasOne(professionnel)
    hasMany(parcelles, proprietaire_id)
    hasMany(verifications, demandeur_id)
    hasMany(roleRequests)
    hasMany(dossiersTransaction as vendeur, vendeur_id)
    hasMany(dossiersTransaction as acheteur, acheteur_id)
    hasMany(dossiersTransaction as dossiersNotaire, notaire_id)
    hasMany(demandesAchat, acheteur_id)
    hasMany(verificationsIdentite, user_id)
    hasMany(confirmationsRendezVous, user_id)
    hasMany(dossierIntervenants)
    hasMany(interventionsGeometre, geometre_id)
    hasMany(interventionsGeometre as assignationsGeometre, assigne_par)
    hasMany(coffreDossiers)
    hasMany(supportTickets)
    belongsToMany(notifications, notifiable)
    hasMany(demandeMessages, sender_id)
    hasMany(demandesServiceGeometre, citoyen_id)
    hasMany(demandesServiceGeometre as demandesRecues, geometre_id)

professionnels
  id, user_id (unique), type (enum: geometre|notaire),
  cabinet, zone_intervention, specialites (json), note_moyenne (decimal), timestamps

  relations:
    belongsTo(user)
    hasMany(avisProfessionnel)

role_requests
  id, user_id, role_demande (enum: geometre|notaire),
  document_justificatif (path), statut (enum: en_attente|valide|rejete, defaut en_attente),
  valide_par (nullable user_id), timestamps

avis_professionnel
  id, professionnel_id, auteur_id (user), dossier_id (nullable FK dossiers_transaction),
  note (int 1-5), commentaire (string 500), timestamps
```

#### localisation
```
communes
  id, nom, timestamps
arrondissements
  id, nom, commune_id
quartiers
  id, nom, arrondissement_id
```

#### parcelles
```
parcelles
  id, proprietaire_id (user), code (string unique, ex: FS-00001), titre, description,
  commune_id, arrondissement_id, quartier_id,
  superficie (decimal), latitude (decimal), longitude (decimal),
  prix_estimatif (decimal),
  statut (enum: libre|en_demande|en_transaction|vendue, defaut libre),
  deleted_at (soft), timestamps

  relations:
    belongsTo(proprietaire)
    belongsTo(commune), belongsTo(arrondissement), belongsTo(quartier)
    hasMany(parcelleDocuments)
    hasMany(verifications)
    hasMany(dossiersTransaction)
    hasMany(demandesAchat)

  Notes:
    - statut 'en_demande' : demande d'achat acceptee, en attente d'ouverture dossier
    - statut 'en_transaction' : dossier cree par le notaire, parties validees
    - statut 'vendue' : dossier clos
    - Les anciens statuts 'conteste' et 'litige' ont ete supprimes
    - Le 'code' est auto-genere au format FS-XXXXX

parcelle_documents
  id, parcelle_id, type_document (enum: tf|adc|plan_topo|photo|autre),
  nom_fichier, chemin_fichier, hash_sha256, taille (int), uploaded_at, timestamps
```

#### demandes-achat (avec messagerie)
```
demandes_achat
  id, parcelle_id, acheteur_id (user), message (text),
  statut (enum: soumise|acceptee|refusee, defaut soumise),
  notaire_id (nullable user), timestamps

  relations:
    belongsTo(parcelle)
    belongsTo(acheteur)
    belongsTo(notaire)
    hasMany(documentsDemande)
    hasMany(messages)

  Logique metier:
    - L'acheteur soumet une demande sur une parcelle libre
    - Le vendeur repond (accepte/refuse) en choisissant un notaire si accepte
    - Une fois acceptee, le notaire peut creer le dossier de transaction
    - L'acheteur et le vendeur peuvent echanger des messages avant l'acceptation

demande_messages
  id, demande_id, sender_id (user), contenu (text), timestamps

  relations:
    belongsTo(demande)
    belongsTo(sender)

documents_demande
  id, demande_id, type_document (enum: piece_identite|autre),
  nom_fichier, chemin_fichier, hash_sha256, taille, uploaded_at, timestamps
```

#### verifications
```
verifications
  id, parcelle_id, demandeur_id (user), titre,
  statut (enum: soumise|en_analyse|terminee, defaut soumise),
  score_risque (int), niveau_risque (enum: faible|moyen|eleve),
  rapport_path, deleted_at (soft), timestamps

  relations:
    belongsTo(parcelle)
    belongsTo(demandeur)
    hasMany(documentsVerification)
    hasMany(analysesAutomatiques)
    hasOne(interventionGeometre)

documents_verification
  id, verification_id, type_document (string: tf|adc|plan_topo|photo|autre),
  nom_fichier, chemin_fichier, hash_sha256, taille, uploaded_at, timestamps

analyses_automatiques
  id, verification_id, type_analyse (string), resultat (string), details (json), timestamps
```

#### transactions — Notaire Central
```
dossiers_transaction
  id, notaire_id (FK users), vendeur_id, acheteur_id, parcelle_id,
  titre, statut (enum: en_attente|cree|en_verification|geometre_assigne|
                     rendezvous_planifie|valide|acte_signe|mutation_en_cours|
                     suspendu|cloture, defaut en_attente),
  motif_suspension (text nullable), closed_at, deleted_at (soft), timestamps

  relations:
    belongsTo(notaire), belongsTo(vendeur), belongsTo(acheteur)
    belongsTo(parcelle)
    hasMany(validations)
    hasMany(dossierIntervenants)
    hasMany(dossierDocuments)
    hasMany(dossierActivites)
    hasMany(dossierMessages)
    hasMany(verificationsIdentite)
    hasMany(rendezVous)
    hasMany(assignationsGeometre, dossier_id)
    hasMany(factures)

  Logique metier:
    - SEUL le notaire peut creer/avancer/suspendre/reouvrir un dossier
    - statut 'en_attente' → validation des 2 parties → 'cree'
    - Le geometre est OPTIONNEL : si aucun assigne, l'etape est sautee
    - Le notaire peut suspendre (avec motif) depuis n'importe quel statut sauf cloture
    - La reouverture ramene a 'en_verification'

validations_dossier
  id, dossier_id, user_id, valide_le (timestamp), timestamps
  unique(dossier_id, user_id)

  relations:
    belongsTo(dossier)
    belongsTo(user)

verifications_identite
  id, dossier_id, user_id, document_verifie (string),
  statut (string: verifie|rejete, defaut en_attente),
  verifie_par (nullable user_id), timestamps

rendez_vous
  id, dossier_id, type (enum: signature|visite_terrain),
  date_prevue (datetime), lieu (string),
  statut (string: planifie|confirme|effectue|annule, defaut planifie), timestamps

  relations:
    belongsTo(dossier)
    hasMany(confirmations)

confirmations_rendez_vous
  id, rendez_vous_id, user_id, est_confirme (bool, defaut false), timestamps
  unique(rendez_vous_id, user_id)

dossier_intervenants
  id, dossier_id, user_id, role_dossier (string: notaire|geometre),
  invite_par (user_id), accepted_at (nullable), timestamps

dossier_documents
  id, dossier_id, uploaded_by (user_id), nom_fichier, chemin_fichier,
  hash_sha256, type_document (nullable string), version (int, defaut 1), timestamps

dossier_messages
  id, dossier_id, sender_id (user), contenu, timestamps

dossier_activites
  id, dossier_id, user_id, action (string), details (json nullable), timestamps

interventions_geometre
  id, dossier_id (FK dossiers_transaction), verification_id (nullable int),
  geometre_id (user), mission (string), assigne_par (user_id),
  statut (string: assigne|en_cours|rapport_recu|annule, defaut assigne),
  rapport_path, avis (string: favorable|defavorable), commentaire,
  completed_at (datetime), timestamps

  relations:
    belongsTo(dossier)
    belongsTo(verification)
    belongsTo(geometre, geometre_id)
    belongsTo(assigneur, assigne_par)

factures
  id, dossier_id (FK), emetteur_id (user),
  reference (string, unique), montant (decimal 12,2),
  description (text), statut (string: brouillon|envoyee|payee, defaut brouillon),
  envoyee_le (datetime nullable), payee_le (datetime nullable), timestamps

  relations:
    belongsTo(dossier)
    belongsTo(emetteur)
```

#### services geometre (indépendant)
```
demande_services_geometre
  id, citoyen_id (user), geometre_id (user), parcelle_id (nullable FK),
  titre, description (text), statut (string: soumise|acceptee|refusee|terminee, defaut soumise),
  rapport_path, commentaire_geometre (text), completed_at (datetime), timestamps

  relations:
    belongsTo(citoyen)
    belongsTo(geometre)
    belongsTo(parcelle)
```

#### coffre-fort
```
coffre_dossiers
  id, user_id, titre, description (text), timestamps

coffre_documents
  id, dossier_id, nom_fichier, chemin_fichier, hash_sha256,
  version (int, defaut 1), taille (int), type_mime, timestamps

coffre_partages
  id, document_id, partage_avec (email), token (string unique),
  expire_le (datetime nullable), timestamps
```

#### notifications
```
notifications
  id (uuid), type (string), notifiable_type (string, defaut 'App\Models\User'),
  notifiable_id (int), data (json), read_at (nullable), timestamps
```

#### blockchain
```
blockchain_logs
  id, action (string), user_id (nullable), module (string), reference_id (nullable),
  metadata (json), previous_hash (string, nullable), current_hash (string, 64), timestamps
```

#### support tickets
```
support_tickets
  id, user_id (FK), sujet (string 200), message (text),
  statut (string: ouvert|en_cours|resolu|ferme, defaut ouvert),
  priorite (string: basse|normale|haute|urgente, defaut normale),
  assigned_to (nullable FK users), reponse (text nullable),
  closed_at (datetime nullable), timestamps
```

---

## 2. Logique métier transversale

### 2.1 Documents de parcelle
- Les documents d'une parcelle sont **bloqués par défaut** pour le public
- Ils ne sont visibles que si l'utilisateur est :
  - le propriétaire de la parcelle
  - un administrateur
  - un utilisateur ayant initié une vérification sur cette parcelle
- Le champ `documents_visibles` (bool) est renvoyé dans la réponse de `GET /api/parcelles/{parcelle}`

### 2.2 Authentification optionnelle
- `GET /api/parcelles` et `GET /api/parcelles/{id}` acceptent un Bearer token optionnel
- Si fourni, le backend identifie l'utilisateur pour filtrer/afficher les documents

### 2.3 Workflow — Notaire Central
1. **Publication** : le propriétaire publie une parcelle (`statut = libre`)
2. **Demande d'achat** : l'acheteur soumet une demande (`POST /api/demandes-achat`) — peut échanger des messages avec le vendeur
3. **Réponse vendeur** : le vendeur accepte/refuse en choisissant un notaire (parcelle → `en_demande`)
4. **Création dossier** : le notaire crée le dossier → `en_attente`
5. **Validation parties** : vendeur + acheteur valident → `cree` (parcelle → `en_transaction`)
6. **Avancer** : le notaire utilise `avancerEtape` pour chaque jalon :
   - `cree` → `en_verification`
   - `en_verification` → `geometre_assigne` (si géomètre assigné) ou `rendezvous_planifie` (skip géomètre)
   - `geometre_assigne` → (rapport géomètre) → `rendezvous_planifie`
   - `rendezvous_planifie` → `valide` (vérifie identités + analyse documentaire automatique)
   - `valide` → (générer acte) → `acte_signe`
   - `acte_signe` → `mutation_en_cours`
   - `mutation_en_cours` → `cloture` (parcelle → `vendue`, bonus indice confiance)
7. **Suspendre** : le notaire peut suspendre avec motif (à tout moment sauf `cloture`)
8. **Réouvrir** : le notaire réouvre vers `en_verification`

### 2.4 Validation des identités (notaire)
- Le notaire vérifie les pièces d'identité via `POST .../verifier-identite`
- Les deux parties (vendeur + acheteur) doivent être vérifiées avant le passage à `valide`
- Vérification faite dans `avancerEtape` au passage à `valide`

### 2.5 Rendez-vous
- **Le notaire, le vendeur ou l'acheteur** peuvent créer un rendez-vous
- Si c'est le notaire qui crée, le dossier passe à `rendezvous_planifie`
- Toutes les parties (vendeur, acheteur, notaire) confirment via `POST .../confirmer`
- Le statut passe à `confirme` quand tous ont confirmé

### 2.6 Géomètre optionnel
- Le notaire peut assigner un géomètre pendant `en_verification`
- Le géomètre dépose son rapport (PDF) avec avis favorable/défavorable
- **Si aucun géomètre n'est assigné**, `avancerEtape` saute `geometre_assigne` mais garde `rendezvous_planifie`

### 2.7 Facturation
- Le notaire crée/modifie des factures sur un dossier (brouillon)
- Envoie la facture (→ `envoyee`)
- Confirme le paiement (→ `payee`)
- Téléchargement PDF avec référence unique

### 2.8 Service géomètre indépendant
- Un citoyen peut commander un service de géomètre **sans lien avec un dossier de transaction**
- Workflow : soumise → acceptee/refusee → terminee (avec rapport PDF)

### 2.9 Notation des professionnels
- Après clôture, le vendeur ou l'acheteur peut noter un professionnel (notaire/géomètre) sur 5
- Une seule note par professionnel par dossier
- La note moyenne du professionnel est recalculée automatiquement

### 2.10 Suspension / réouverture
- Seul le notaire responsable peut suspendre (avec motif obligatoire)
- Traçage blockchain de chaque suspension et réouverture
- Un dossier suspendu ne peut pas être avancé

### 2.11 Indice de confiance
- Chaque utilisateur a un indice de confiance (0–100)
- +15 pour transaction réussie (vendeur + acheteur)
- +10 pour le notaire à la clôture

### 2.12 Hash SHA-256
- Tout document uploadé est haché avant stockage
- Le hash est vérifiable via les endpoints d'intégrité

### 2.13 Blockchain
- Actions critiques loggées avec hash SHA-256 chaîné
- Modules : `transaction`, `verification`, `coffre`, `admin`, `auth`, `service_geometre`, `facture`, `support`

### 2.14 Chiffrement
- Documents stockés chiffrés avec `EncryptionHelper` (AES-256-CBC)
- Stockage dans `storage/app/` avec chemins structurés par entité

---

## 3. Routes API complètes (v8 — 111 routes)

Toutes les routes sont préfixées par `/api`.

### 3.1 Publiques (aucun auth requis)

```
POST   /auth/register                     Payload: nom, prenom, email, telephone, password, password_confirmation
POST   /auth/login                        Payload: email, password
POST   /auth/forgot-password              Payload: email → envoie OTP 6 chiffres
POST   /auth/reset-password               Payload: email, otp, password, password_confirmation

GET    /localisation/communes             → [ Commune { id, nom } ]
GET    /localisation/arrondissements/{commune}   → [ Arrondissement { id, nom } ]
GET    /localisation/quartiers/{arrondissement}  → [ Quartier { id, nom } ]

GET    /parcelles                          → paginé [ Parcelle { ..., proprietaire, commune, arrondissement, quartier } ]
                                            Query: statut, commune_id, search, page
                                            Note: documents NON chargés, auth optionnelle

GET    /parcelles/{parcelle}               → Parcelle { ..., proprietaire, commune, arrondissement, quartier,
                                                         verifications, documents_visibles (bool),
                                                         documents [...] (si autorisé) }
                                            Note: Bearer token optionnel

GET    /cartographie/couches               Query: periode (all|1mois|6mois|1an)
                                            → { parcelles (lat/lng), professionnels, activite_recente }

GET    /observatoire                       → { transactions, verifications, evolution_mensuelle, prix_moyen }

GET    /professionnels                     Query: type, role, search, zone, specialite, commune_id
GET    /professionnels/{professionnel}     → Professionnel { ..., user, avis }

GET    /blockchain/verifier                → { valid: bool, messages: [...] }
```

### 3.2 Authentifiées (Bearer token requis — tous les rôles)

```
POST   /auth/logout
GET    /auth/profile                       → User { ..., professionnel }
PUT    /auth/profile                       Payload: nom, prenom, telephone (optionnels)
POST   /auth/request-role                  Payload: role_demande (geometre|notaire), document_justificatif (file)

GET    /notifications                      → paginé [ Notification ]
PATCH  /notifications/{notification}/read  → Notification (read_at mis à jour)
GET    /notifications/non-lues             → { non_lues: int }

GET    /transactions                       → paginé [ DossierTransaction { ..., vendeur, acheteur, notaire, parcelle, intervenants } ]
                                            Query: statut, search
                                            Note: citoyen voit ses dossiers (vendeur/acheteur),
                                                  notaire voit ses dossiers assignés,
                                                  géomètre voit ceux où il intervient

GET    /transactions/{dossierTransaction}   → DossierTransaction { ..., vendeur, acheteur, notaire, parcelle,
                                                                     intervenants.user, documents.uploader,
                                                                     activites.user, messages.sender,
                                                                     verificationsIdentite.user, verificationsIdentite.verifiePar,
                                                                     rendezVous.confirmations.user,
                                                                     assignationsGeometre.geometre, assignationsGeometre.assigneur,
                                                                     factures }

GET    /transactions/{dossierTransaction}/documents    → [ DossierDocument { ..., uploader } ]
GET    /transactions/{dossierTransaction}/messages     → [ DossierMessage { ..., sender } ]

GET    /demandes-achat                                → paginé [ DemandeAchat { ..., parcelle, acheteur, notaire } ]
GET    /demandes-achat/{demandeAchat}                  → DemandeAchat { ..., parcelle, acheteur, notaire, documents, messages }
GET    /demandes-achat/{demandeAchat}/messages         → [ DemandeMessage { ..., sender } ]

GET    /verifications                        → paginé [ Verification { ..., demandeur, parcelle, documents, analyses, intervention } ]
GET    /verifications/{verification}         → Verification { ..., demandeur, parcelle, documents, analyses, intervention.geometre }

GET    /coffre/dossiers                      → [ CoffreDossier { ..., documents } ]

GET    /blockchain                           → paginé [ BlockchainLog { ..., user } ]
GET    /blockchain/module/{module}/{referenceId?}  → [ BlockchainLog ]

GET    /support/tickets                      → paginé [ SupportTicket { ..., user, assigne } ]
                                            Note: citoyen voit ses tickets, admin voit tous
POST   /support/tickets                     Payload: sujet, message, priorite (optionnelle)
GET    /support/tickets/{supportTicket}      → SupportTicket { ..., user, assigne }
```

### 3.3 Rôle citoyen

```
POST   /parcelles                            Payload: titre, description, commune_id, arrondissement_id, quartier_id,
                                                   superficie, latitude, longitude, prix_estimatif
PUT    /parcelles/{parcelle}                  Payload: (même structure, tous optionnels)
POST   /parcelles/{parcelle}/documents        Payload: type_document, fichier (file)
DELETE /parcelles/{parcelle}/documents/{id}   → 204
PATCH  /parcelles/{parcelle}/statut           Payload: statut

POST   /demandes-achat                        Payload: parcelle_id, message
PATCH  /demandes-achat/{demandeAchat}/repondre   Payload: statut (acceptee|refusee), notaire_id (si acceptée)
POST   /demandes-achat/{demandeAchat}/documents   Payload: type_document, fichier (file)
POST   /demandes-achat/{demandeAchat}/messages    Payload: contenu

POST   /transactions/{dossierTransaction}/valider-partie   → valide en tant que vendeur ou acheteur
POST   /transactions/{dossierTransaction}/messages          Payload: contenu
POST   /transactions/{dossierTransaction}/documents         Payload: fichier (file)

POST   /professionnels/{professionnel}/avis   Payload: note (1-5), commentaire

POST   /services-geometre                     Payload: geometre_id, parcelle_id (optionnel), titre, description
GET    /services-geometre                     → paginé [ DemandeServiceGeometre ]
GET    /services-geometre/{demandeServiceGeometre}  → DemandeServiceGeometre { ..., citoyen, geometre, parcelle }

POST   /transactions/{dossierTransaction}/rendez-vous     Payload: type, date_prevue, lieu

POST   /transactions/{dossierTransaction}/noter           Payload: professionnel_user_id, note (1-5), commentaire (optionnel)
                                                          Note: uniquement après clôture

POST   /coffre/dossiers                       Payload: titre, description
POST   /coffre/dossiers/{coffreDossier}/documents  Payload: fichier (file)
GET    /coffre/documents/{id}/telecharger     → file stream (déchiffré)
POST   /coffre/documents/{id}/partager        Payload: email, expire_le
GET    /coffre/documents/{id}/integrite       → { document_id, nom_fichier, integrite: bool }
```

### 3.4 Rôle notaire

```
POST   /transactions                         Payload: parcelle_id, vendeur_id, acheteur_id, titre
                                            Note: parcelle doit être en 'en_demande'

POST   /transactions/{dossierTransaction}/assigner-geometre   Payload: geometre_id, mission
POST   /transactions/{dossierTransaction}/verifier-identite    Payload: user_id, document_verifie, statut (verifie|rejete)
POST   /transactions/{dossierTransaction}/valider              Payload: — (délègue à avancerEtape)
POST   /transactions/{dossierTransaction}/avancer              → avance d'un jalon (vérifie identités au passage à valide)
POST   /transactions/{dossierTransaction}/generer-acte         → génère acte PDF, avance à 'acte_signe'
POST   /transactions/{dossierTransaction}/export-pdf           → export PDF du dossier complet

POST   /transactions/{dossierTransaction}/inviter             Payload: user_id (ou email), role_dossier (notaire|geometre)
GET    /transactions/{dossierTransaction}/intervenants        → [ DossierIntervenant ]

POST   /transactions/{dossierTransaction}/suspendre           Payload: motif (obligatoire)
POST   /transactions/{dossierTransaction}/reouvrir            Payload: —

POST   /transactions/{dossierTransaction}/rendez-vous         Payload: type, date_prevue, lieu
PATCH  /rendez-vous/{rendezVous}                              Payload: statut (confirme|effectue|annule)

GET    /transactions/{dossierTransaction}/factures            → [ Facture ]
POST   /transactions/{dossierTransaction}/factures            Payload: montant, description
GET    /factures/{facture}                                    → Facture { ..., emetteur, dossier }
PUT    /factures/{facture}                                    Payload: montant, description
POST   /factures/{facture}/envoyer                            → statut → envoyee
POST   /factures/{facture}/payer                              → statut → payee
GET    /factures/{facture}/pdf                                → téléchargement PDF

PUT    /professionnels/{professionnel}        Payload: cabinet, zone_intervention, specialites
```

### 3.5 Rôle géomètre

```
GET    /geometre/missions                     → [ InterventionGeometre { ..., verification, dossier } ]

POST   /transactions/{dossierTransaction}/interventions/{intervention}/rapport
                                             Payload: fichier (PDF), avis (favorable|defavorable), commentaire

POST   /verifications/{verification}/rapport-geometre   Payload: fichier (file), avis, commentaire
GET    /verifications/{verification}/rapport  → file stream (PDF)

POST   /services-geometre/{demandeServiceGeometre}/accepter  → statut → acceptee
POST   /services-geometre/{demandeServiceGeometre}/refuser    → statut → refusee
POST   /services-geometre/{demandeServiceGeometre}/rapport    Payload: rapport (PDF), commentaire
GET    /services-geometre                     → paginé [ DemandeServiceGeometre ]

PUT    /professionnels/{professionnel}        Payload: cabinet, zone_intervention, specialites
```

### 3.6 Rôle admin

```
GET    /admin/dashboard                       → { total_users, total_citoyens, total_geometres,
                                                   total_notaires, pending_role_requests, recent_users }
GET    /admin/users                           → paginé [ User { ..., professionnel } ]
PATCH  /admin/users/{user}/toggle-status      → User (is_active toggled)
GET    /admin/role-requests                   → paginé [ RoleRequest { ..., user, valideur } ]
PATCH  /admin/role-requests/{roleRequest}     Payload: action (valide|rejete)

POST   /admin/localisation/communes           Payload: nom
PUT    /admin/localisation/communes/{id}      Payload: nom
DELETE /admin/localisation/communes/{id}      → 204 (cascade)
POST   /admin/localisation/arrondissements    Payload: nom, commune_id
PUT    /admin/localisation/arrondissements/{id}   Payload: nom, commune_id
DELETE /admin/localisation/arrondissements/{id}   → 204 (cascade)
POST   /admin/localisation/quartiers          Payload: nom, arrondissement_id
PUT    /admin/localisation/quartiers/{id}     Payload: nom, arrondissement_id
DELETE /admin/localisation/quartiers/{id}     → 204

GET    /blockchain                            → paginé [ BlockchainLog ]
GET    /blockchain/module/{module}/{referenceId?}  → [ BlockchainLog ]

POST   /support/tickets/{supportTicket}/repondre  Payload: reponse, statut (optionnel)
PATCH  /support/tickets/{supportTicket}/statut    Payload: statut (ouvert|en_cours|resolu|ferme)
```

### 3.7 Acceptation invitation (tous les rôles)

```
POST   /transactions/{dossierTransaction}/invitations/{intervenant}/accepter
POST   /transactions/{dossierTransaction}/invitations/{intervenant}/refuser
```

---

## 4. Notes importantes pour le frontend

### 4.1 Pagination
Tous les index endpoints utilisent la pagination Laravel :
```json
{
  "data": [...],
  "current_page": 1, "last_page": 5, "per_page": 15, "total": 72,
  "from": 1, "to": 15, "links": [...]
}
```

### 4.2 Codes HTTP
| Code | Signification |
|------|---------------|
| 200 | Succès |
| 201 | Créé |
| 204 | Supprimé (pas de contenu) |
| 400 | Requête invalide |
| 401 | Non authentifié |
| 403 | Accès refusé (rôle insuffisant ou action non autorisée) |
| 404 | Ressource introuvable |
| 409 | Conflit (doublon) |

### 4.3 Middleware par groupe de routes
- `auth:sanctum` : authentification requise
- `role:citoyen`, `role:geometre`, `role:notaire`, `role:admin`
- Combinaisons : `role:citoyen,geometre` (citoyen OU geometre)

### 4.4 Workflow complet — enchaînement frontend

```
[Citoyen A] publie parcelle → POST /parcelles
      ↓
[Citoyen B] soumet demande achat → POST /demandes-achat
      ↓
[Citoyen A] répond avec choix notaire → PATCH /demandes-achat/{id}/repondre
      ↓                         (parcelle → 'en_demande')
[Notaire]   crée dossier → POST /transactions (statut → 'en_attente')
      ↓
[Vendeur + Acheteur] valident → POST .../valider-partie (statut → 'cree', parcelle → 'en_transaction')
      ↓
[Notaire]   avancerEtape → POST .../avancer (statut → 'en_verification')
      ↓   ┌────────────────────────────────────┐
          │   Optionnel : assigner géomètre     │
          │   POST .../assigner-geometre        │
          │   → géomètre dépose rapport         │
          │   POST .../interventions/{id}/rapport│
          └──────────────┬─────────────────────┘
                         ↓
[Notaire/Vendeur/Acheteur] planifie RDV → POST .../rendez-vous
      ↓
[Tous] confirment RDV → POST /rendez-vous/{id}/confirmer
      ↓
[Notaire]   avancerEtape → POST .../avancer
            (vérifie identités + analyse auto → 'valide')
      ↓
[Notaire]   génère acte → POST .../generer-acte ('acte_signe')
      ↓
[Notaire]   avancerEtape → POST .../avancer ('mutation_en_cours')
      ↓
[Notaire]   avancerEtape → POST .../avancer ('cloture', parcelle → 'vendue')
      ↓
[Acheteur/Vendeur] notent pro → POST .../noter
```

**Suspension possible à tout moment** :
```
[Notaire]   suspendre → POST .../suspendre (motif obligatoire)
[Notaire]   réouvrir  → POST .../reouvrir (retour à 'en_verification')
```

**Si pas de géomètre** : `avancerEtape` depuis `en_verification` saute `geometre_assigne` et va directement à `rendezvous_planifie`.

### 4.5 Relations à charger selon la page

| Page | Endpoint | Relations incluses |
|------|----------|--------------------|
| Liste parcelles (publique) | `GET /parcelles` | proprietaire, commune, arrondissement, quartier |
| Détail parcelle | `GET /parcelles/{id}` | + verifications, documents (si autorisé) |
| Liste vérifications | `GET /verifications` | demandeur, parcelle, documents, analyses, intervention |
| Détail vérification | `GET /verifications/{id}` | + intervention.geometre |
| Liste transactions | `GET /transactions` | vendeur, acheteur, notaire, parcelle, intervenants |
| Détail transaction | `GET /transactions/{id}` | + documents.uploader, activites.user, messages.sender, verificationsIdentite.user/verifiePar, rendezVous.confirmations.user, assignationsGeometre.geometre/assigneur, factures |
| Liste demandes-achat | `GET /demandes-achat` | parcelle, acheteur, notaire |
| Détail demande-achat | `GET /demandes-achat/{id}` | + documents, messages |
| Messages demande | `GET /demandes-achat/{id}/messages` | sender |
| Factures dossier | `GET /transactions/{id}/factures` | emetteur |
| Services géomètre | `GET /services-geometre` | citoyen, geometre, parcelle |
| Cartographie | `GET /cartographie/couches` | lat/lng depuis parcelles |
| Observatoire | `GET /observatoire` | statistiques |
| Profils pro | `GET /professionnels` / `GET /professionnels/{id}` | user, avis |
| Coffre | `GET /coffre/dossiers` | documents |
| Notifications | `GET /notifications` | — |
| Blockchain | `GET /blockchain` | user |
| Tickets support | `GET /support/tickets` | user, assigne |

### 4.6 Particularités

1. **`POST /api/transactions`** est réservé au **notaire** (403 si citoyen) ; la parcelle doit être en statut `en_demande`
2. Les **documents de parcelle** sont invisibles sans auth appropriée
3. **`GET /api/parcelles`** accepte un Bearer token optionnel
4. Le **géomètre est optionnel** : si aucun géomètre n'est assigné quand le dossier est en `en_verification`, l'étape `geometre_assigne` est sautée mais `rendezvous_planifie` reste obligatoire
5. **Code parcelle** (`code` dans la réponse) : identifiant unique format `FS-XXXXX`, auto-généré. Utilisable via `?search=FS-00042`
6. **Signalement et Médiation** ont été complètement supprimés de l'architecture — le notaire investigateur gère les litiges hors plateforme (suspension du dossier si nécessaire)
7. **Factures** : cycle brouillon → envoyée → payée ; seul le notaire peut créer/envoyer/confirmer le paiement
8. **Services géomètre indépendants** : un citoyen peut commander un levé topographique sans lien avec un dossier de transaction
9. **Notation** : accessible uniquement après clôture du dossier, une seule note par professionnel par dossier
10. **Support tickets** : ouverts par tout utilisateur, traités par les admins uniquement
11. **Suspension** : tracée dans la blockchain avec le motif ; un dossier suspendu ne peut pas être avancé
