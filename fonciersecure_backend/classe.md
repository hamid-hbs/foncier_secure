Diagramme de Classes — FoncierSecure
Règles de Gestion (RGs)
RG	Description
RG-01	Seuls les citoyens peuvent posséder des parcelles
RG-02	Seul l'administrateur valide les changements de rôle
RG-03	Une parcelle a un seul propriétaire à la fois
RG-04	Tout document doit être rattaché à une entité
RG-05	Un notaire est assigné à une demande de type achat
RG-06	Un dossier de transaction suit un cycle d'étapes
RG-07	Les messages sont attachés à un service ou un dossier
RG-08	Un dossier nécessite validation des deux parties
RG-09	Les avis sont laissés par les citoyens sur les pros
RG-10	Les documents peuvent être dans le coffre utilisateur
RG-11	Un géomètre est assigné à un service de type géomètre
RG-12	Les tickets de support sont traités par les admins
RG-13	Toute action importante est journalisée
RG-14	Les documents sont hashés pour intégrité
1. Utilisateur (superclasse)
Attributs :
- id : int
- nom : string
- prenom : string
- email : string (unique)
- telephone : string
- motDePasse : string
- photoProfil : string (nullable)
- statut : boolean
- dateCreation : datetime
Méthodes :
- seConnecter(email, motDePasse) : bool
- seDeconnecter() : void
- modifierProfil(donnees) : bool
- changerMotDePasse(ancien, nouveau) : bool
- recevoirNotification(notification) : void
- consulterNotifications() : Notification[]
- ouvrirTicket(sujet, description, priorite) : TicketSupport
Relations :
- 1..1 → Citoyen / Notaire / Géomètre / Administrateur (héritage)
- 1 → 0..* Notification (destinataire)
- 1 → 0..* Message (expéditeur)
- 1 → 0..* TicketSupport (créateur)
- 1 → 0..* Journal (acteur)
2. Citoyen (héritage Utilisateur)
Attributs :
- numeroPiece : string
- adresse : string
- profession : string
- dateNaissance : date
Méthodes :
- publierParcelle(donnees) : Parcelle
- modifierParcelle(parcelle, donnees) : bool
- consulterParcelles() : Parcelle[]
- demanderService(type, donnees) : Service
- repondreDemande(service, acceptee, notaire?) : bool
- consulterServices() : Service[]
- validerDossierTransaction(dossier) : bool
- prendreRendezVous(dossier/service, date, lieu) : RendezVous
- confirmerRendezVous(rendezVous) : bool
- laisserAvis(professionnel, note, commentaire) : Avis
- uploadDocument(entite, fichier) : Document
- consulterDocuments(entite) : Document[]
Relations :
- 1 → 0..* Parcelle (propriétaire)
- 1 → 0..* Service (demandeur)
- 1 → 0..* DossierTransaction (vendeur)
- 1 → 0..* DossierTransaction (acheteur)
- 1 → 0..* Propriete
- 1 → 0..* Avis (auteur)
3. Notaire (héritage Utilisateur)
Attributs :
- numeroAgrement : string
- nomEtude : string
- adresseProfessionnelle : string
- specialite : string
- noteMoyenne : float
Méthodes :
- accepterDemande(service) : DossierTransaction
- refuserDemande(service, motif) : bool
- ouvrirDossier(donnees) : DossierTransaction
- consulterDossiers() : DossierTransaction[]
- validerDocuments(dossier) : bool
- assignerGeometre(dossier, geometre, mission) : bool
- planifierRendezVous(dossier, date, lieu, type) : RendezVous
- genererActeVente(dossier) : Document
- avancerDossier(dossier) : bool
- suspendreDossier(dossier, motif) : bool
- rouvrirDossier(dossier) : bool
- genererFacture(dossier, montant) : Facture
- consulterFactures(dossier) : Facture[]
- modifierProfilProfessionnel(donnees) : bool
Relations :
- 1 → 0..* Service (type=achat)
- 1 → 0..* DossierTransaction (responsable)
- 1 → 0..* Facture (émetteur)
- 1 → 0..* Avis (concerné)
4. Géomètre (héritage Utilisateur)
Attributs :
- numeroAgrement : string
- cabinet : string
- specialite : string
- noteMoyenne : float
Méthodes :
- consulterMissions() : Service[] + DossierTransaction[]
- accepterMission(service) : bool
- refuserMission(service) : bool
- effectuerVerification(entite) : bool
- soumettreRapport(entite, rapport, avis) : bool
- deposerRapportService(service, rapport) : bool
- modifierProfilProfessionnel(donnees) : bool
Relations :
- 1 → 0..* Service (type=service_geometre)
- 1 → 0..* DossierTransaction (assigné)
- 1 → 0..* Avis (concerné)
5. Administrateur (héritage Utilisateur)
Attributs :
- matricule : string
- niveauAcces : string
Méthodes :
- consulterTableauBord() : Statistiques
- gererUtilisateurs() : Utilisateur[]
- activerCompte(utilisateur) : bool
- desactiverCompte(utilisateur) : bool
- validerProfessionnel(demande) : bool
- rejeterProfessionnel(demande) : bool
- traiterTicket(ticket, reponse) : bool
- cloreTicket(ticket) : bool
- consulterJournal() : Journal[]
- creerCommune(nom) : Commune
- creerArrondissement(commune, nom) : Arrondissement
- creerQuartier(arrondissement, nom) : Quartier
Relations :
- 1 → 0..* TicketSupport (répondant)
6. Commune
Attributs :
- id : int
- nom : string
- code : string
Méthodes :
- getArrondissements() : Arrondissement[]
Relations :
- 1 → 1..* Arrondissement
7. Arrondissement
Attributs :
- id : int
- nom : string
- code : string
Méthodes :
- getCommune() : Commune
- getQuartiers() : Quartier[]
Relations :
- *..1 Commune
- 1 → 1..* Quartier
8. Quartier
Attributs :
- id : int
- nom : string
Méthodes :
- getArrondissement() : Arrondissement
- getParcelles() : Parcelle[]
Relations :
- *..1 Arrondissement
- 1 → 0..* Parcelle
9. Parcelle
Attributs :
- id : int
- referenceCadastrale : string (unique)
- superficie : float
- adresse : string
- coordonneesGPS : string (nullable)
- statut : enum(libre, en_demande, en_transaction, vendue)
Méthodes :
- getProprietaire() : Citoyen
- modifierStatut(statut) : bool
- consulterHistoriquePropriete() : Propriete[]
- getDocuments() : Document[]
- getDossiers() : DossierTransaction[]
Relations :
- *..1 Quartier
- *..1 Citoyen (propriétaire)
- 1 → 0..* Document
- 1 → 0..* Propriete
- 1 → 0..* DossierTransaction
10. Propriete (classe d'association)
Attributs :
- id : int
- dateDebut : date
- dateFin : date (nullable)
- pourcentageDetention : float
- typeAcquisition : string (achat, succession, donation)
Méthodes :
- activer() : bool
- cloturer(dateFin) : bool
- transferer(nouveauProprietaire) : Propriete
Relations :
- *..1 Citoyen
- *..1 Parcelle
11. Service
Attributs :
- id : int
- type : enum(achat, service_geometre)
- titre : string
- description : text (nullable)
- statut : enum(soumise, acceptee, refusee, terminee)
- rapportPath : string (nullable)
- commentairePro : text (nullable)
- dateDemande : datetime
- dateCloture : datetime (nullable)
Méthodes :
- soumettre() : bool
- accepter() : bool
- refuser(motif) : bool
- terminer() : bool
- getMessages() : Message[]
- getDocuments() : Document[]
- getRendezVous() : RendezVous[]
- ajouterMessage(contenu) : Message
- uploadDocument(fichier) : Document
Relations :
- *..1 Citoyen (demandeur)
- *..1 Notaire (si type=achat, nullable)
- *..1 Géomètre (si type=service_geometre, nullable)
- 1 → 0..* Document
- 1 → 0..* Message
- 1 → 0..* RendezVous
- 1 → 0..1 DossierTransaction (généré si type=achat accepté)
12. DossierTransaction
Attributs :
- id : int
- numeroDossier : string (unique)
- dateCreation : datetime
- statut : enum(en_attente, cree, en_verification, rendezvous_planifie, valide, acte_signe, cloture, suspendu)
- objet : text
- motifSuspension : text (nullable)
- dateValidationVendeur : datetime (nullable)
- dateValidationAcheteur : datetime (nullable)
- dateCloture : datetime (nullable)
Méthodes :
- ouvrir() : bool
- validerPar(partie) : bool
- avancerEtape() : bool
- suspendre(motif) : bool
- rouvrir() : bool
- cloturer() : bool
- getDocuments() : Document[]
- getMessages() : Message[]
- getRendezVous() : RendezVous[]
- getFactures() : Facture[]
- ajouterDocument(fichier) : Document
- ajouterMessage(contenu) : Message
- getVendeur() : Citoyen
- getAcheteur() : Citoyen
- getNotaire() : Notaire
- getGeometre() : Géomètre (nullable)
Relations :
- *..1 Parcelle
- *..1 Citoyen (vendeur)
- *..1 Citoyen (acheteur)
- *..1 Notaire (responsable)
- *..1 Géomètre (assigné, nullable)
- *..1 Service (demande d'origine)
- 1 → 0..* Document
- 1 → 0..* Message
- 1 → 0..* RendezVous
- 1 → 0..* Facture
- 1 → 0..* Avis
13. Document
Attributs :
- id : int
- nom : string
- typeDocument : string
- cheminFichier : string
- taille : int (nullable)
- dateDepot : datetime
- hashSha256 : string
Méthodes :
- televerser(fichier) : bool
- telecharger() : fichier
- verifierIntegrite() : bool
- getEntiteParent() : (entite)
Relations :
- *..1 (Parcelle / Service / DossierTransaction) — attachable
14. Message
Attributs :
- id : int
- contenu : text
- dateEnvoi : datetime
- lu : boolean
Méthodes :
- envoyer() : bool
- marquerCommeLu() : bool
- getExpediteur() : Utilisateur
Relations :
- *..1 Utilisateur (expéditeur)
- *..1 (Service / DossierTransaction) — attachable
15. RendezVous
Attributs :
- id : int
- type : enum(signature, visite_terrain)
- date : date
- heure : time
- lieu : string (nullable)
- statut : enum(planifie, confirme, effectue, annule)
Méthodes :
- programmer(date, heure, lieu) : bool
- confirmer(participant) : bool
- annuler(motif) : bool
- reporter(nouvelleDate) : bool
- getParticipants() : Utilisateur[]
Relations :
- *..1 Service (nullable)
- *..1 DossierTransaction (nullable)
16. Facture
Attributs :
- id : int
- numero : string (unique)
- montant : decimal
- description : text (nullable)
- statut : enum(brouillon, envoyee, payee)
- dateEmission : datetime
- datePaiement : datetime (nullable)
Méthodes :
- generer() : bool
- envoyer() : bool
- payer() : bool
- annuler() : bool
- telechargerPdf() : fichier
Relations :
- *..1 DossierTransaction
- *..1 Notaire (émetteur)
17. Avis (classe d'association)
Attributs :
- id : int
- note : int (1-5)
- commentaire : text (nullable)
- datePublication : datetime
Méthodes :
- publier() : bool
- modifier(note, commentaire) : bool
- getAuteur() : Citoyen
- getProfessionnel() : Notaire/Géomètre
Relations :
- *..1 Citoyen (auteur)
- *..1 Notaire (concerné, nullable)
- *..1 Géomètre (concerné, nullable)
- *..1 DossierTransaction (lié à, nullable)
- *..1 Service (lié à, nullable)
18. TicketSupport
Attributs :
- id : int
- sujet : string
- description : text
- statut : enum(ouvert, en_cours, resolu, ferme)
- priorite : enum(basse, normale, haute, urgente)
- reponse : text (nullable)
- dateCreation : datetime
- dateCloture : datetime (nullable)
Méthodes :
- ouvrir() : bool
- repondre(reponse) : bool
- changerStatut(statut) : bool
- fermer() : bool
- assigner(administrateur) : bool
Relations :
- *..1 Utilisateur (créateur)
- *..1 Administrateur (répondant, nullable)
19. Notification
Attributs :
- id : int
- titre : string
- contenu : text
- type : string
- lu : boolean
- dateEnvoi : datetime
Méthodes :
- envoyer() : bool
- marquerCommeLue() : bool
Relations :
- *..1 Utilisateur (destinataire)
20. Journal
Attributs :
- id : int
- action : string
- module : string
- referenceId : int (nullable)
- details : text (nullable)
- date : datetime
Méthodes :
- enregistrer(action, module, details) : bool
- getEntrees(module, referenceId) : Journal[]
Relations :
- *..1 Utilisateur (acteur, nullable)
Récapitulatif des classes
#	Classe	Type
1	Utilisateur	superclasse
2	Citoyen	héritée (Utilisateur)
3	Notaire	héritée (Utilisateur)
4	Géomètre	héritée (Utilisateur)
5	Administrateur	héritée (Utilisateur)
6	Commune	simple
7	Arrondissement	simple
8	Quartier	simple
9	Parcelle	simple
10	Propriete	association (Citoyen ↔ Parcelle)
11	Service	simple
12	DossierTransaction	simple
13	Document	simple (polymorphe)
14	Message	simple (polymorphe)
15	RendezVous	simple
16	Facture	simple
17	Avis	association (Citoyen ↔ Notaire/Géomètre)
18	TicketSupport	simple
19	Notification	simple
20	Journal	simple
20 classes : 1 superclasse + 4 héritées + 13 simples + 2 associations