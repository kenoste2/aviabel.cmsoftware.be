ALTER TABLE FILES$DEBTORS ADD CREDIT_LIMIT DOM_MONEY#
create index ix_debtor_credit_limit on FILES$DEBTORS computed by (CREDIT_LIMIT)#

CREATE TABLE IMPORTED_MAILS (
  IMPORTED_MAIL_ID int not null primary key,
  FILE_ID int not null references FILES$FILES,
  CREATION_DATE TIMESTAMP,
  FROM_EMAIL varchar(50),
  TO_EMAIL varchar(50),
  MAIL_BODY varchar(10000),
  MAIL_SUBJECT varchar(3000)
);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('mail_from_c','all','Van','De','From',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('mail_to_c','all','Aan','À','To',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('imported_mails_c','all','E-mails','Emails','Emails',0);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('mail_subject_c','all','Onderwerp','Sujet','Subject',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('menu_imported-mail_overview','all','E-mails','Emails','Emails',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('date_from_c','all','van','de','from',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('date_till_c','all','tot', 'à','until',0);

insert into system$templates_modules
(ID, NL, FR, EN, FILENAME, ACTIEF, VARIABELEN) VALUES(10, 'Verstuur per e-mail', NULL, NULL, NULL, 1, NULL);

insert into system$templates_modules
(ID, NL, FR, EN, FILENAME, ACTIEF, VARIABELEN) VALUES(11, 'Verstuur per SMS', NULL, NULL, NULL, 1, NULL);

alter table files$file_actions
add GSM varchar(250);

alter table files$file_actions
add VIA varchar(250);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('conditions_file_c','all','Bestand met factuurvoorwaarden','Fichier conditions de facturation','Invoice conditions file',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('download_file_c','all','Bestand downloaden','Télécharger fichier','Download file',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('via_c','all','Verstuurd via','Envoyé par','Sent by',0);

update files$file_actions set VIA = 'EMAIL' where EMAIL IS NOT NULL AND EMAIL != '';

update files$file_actions set VIA = 'POST' where ADDRESS IS NOT NULL AND ADDRESS != '';

update files$file_actions fa set VIA = 'POST' where VIA IS NULL and (select first 1 CODE from FILES$ACTIONS a where fa.ACTION_ID = a.ACTION_ID) in ('BRF1', 'BRF2', 'BRF3') and REMARKS like 'train%';

alter table system$templates
add TEXT_SMS_NL varchar(15000);
alter table system$templates
add TEXT_SMS_FR varchar(15000);
alter table system$templates
add TEXT_SMS_EN varchar(15000);
alter table system$templates
add TEXT_SMS_DE varchar(15000);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('textinhoud_sms_c','all','tekstinhoud SMS','contenu SMS','text message content',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('inhoud_sms_c','all','tekstinhoud SMS','contenu SMS','text message content',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('sms_c','all','SMS','SMS','text message',0);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('email_to_c','all','Aan','À','To',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('email_unknown_c','all','E-mailadres onbekend','Adresse e-mail inconnu','Email address unknown',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('client_email_c','all','E-mailadres opdrachtgever','Adresse e-mail du client','Client email address',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('user_email_c','all','E-mailadres gebruiker','Adresse e-mail de l''utilisateur','User email address',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('email_from_c','all','Van','De','From',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('custom_email_c','all','Vrij e-mailadres','Adresse e-mail personnalisée','Custom email address',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('email_custom_from_c','all','Vrij e-mailadres','Adresse e-mail personnalisée','Custom email address',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('subject_c','all','Onderwerp','Sujet','Subject',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('content_c','all','Inhoud','Contenu','Content',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('send_c','all','Verzenden','Envoyer','Send',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('attachments_c','all','Bijlages','Pièces jointes','Attachments',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('no_email_c','all','Geef een correct e-mailadres op.','Saississez une adresse e-mail valable.','Choose a valid email address.',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('success_c','all','De e-mail werd succesvol verstuurd.','L''email à été envoyée correctement.','The email was sent out successfully.',0);


INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('email_documents_c','all','E-maildocumenten','Documents de l''e-mail','Email documents',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('action_documents_c','all','Actiedocumenten.','Documents de l''action','Action documents',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('file_documents_c','all','Dossierdocumenten.','Documents du dossier','File documents',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('no_attachments_found_c','all','Geen bijlages gevonden.','Pas de pièces jointes retrouvées','No attachments found.',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('mail_not_sent_c','all','De mail kon niet uitgestuurd worden. Mogelijk heeft u te veel of te grote attachments toegevoegd. Probeer graag opnieuw met minder attachments.','L''email n''a pas été envoyé. Peut-être vous avez ajouté trop de fichiers en annexe ou des fichiers trop grands. Veuillez ressayer avec moins de fichiers en annexe.','The mail could not be sent out. Possibly this is because you added to many attachments or attachments that are too big. Please retry with fewer attachments.',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('based_on_payments_c','all','gebaseerd op aantal betalingen','basé sur nombre de payements','based on number of payments',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('insufficient_data_for_delay_c','all','nog niet genoeg gegevens.','pas encore assez de données','not enough information available',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('forecastHistogram_c','all','Betalingsvoorspelling','Prédiction de payement','Payment forecast',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('all_clients_c','all','alle klanten','tous les clients','all clients',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('menu_report_payment-forecast','all','Betalingsvoorspelling','Prédiction de payement','Payment forecast',0);

create table DEBTOR_SCORE (
    DEBTOR_SCORE_ID DOM_RECORD_ID,
    DEBTOR_ID DOM_RECORD_ID,
    SCORE INT,
    USER_ID DOM_RECORD_ID,
    TIME_STAMP TIMESTAMP
);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('debtor_score_c','all','Score','Score','Score',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('external_c','all','Extern','Externe','External',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('external_collector_c','all','Externe beheerder','Gestionnaire externe','External caseworker',0);

ALTER TABLE SYSTEM$COLLECTORS
ADD EXTERN CHAR(1) DEFAULT 0;

ALTER TABLE FILES$FILES
ADD EXTERNAL_COLLECTOR_ID DOM_RECORD_ID;
ALTER TABLE FILES$DEBTORS
ADD EXTERNAL_AUTH_CODE VARCHAR(30);

ALTER TABLE FILES$DEBTORS
ADD EXTERNAL_AUTH_EXPIRATION TIMESTAMP;

ALTER TABLE FILES$REFERENCES
ADD DEBTOR_DISPUTE_COMMENT VARCHAR(10000);

ALTER TABLE FILES$REFERENCES
ADD DISPUTE_COMMENT VARCHAR(10000);

ALTER TABLE FILES$REFERENCES
ADD DISPUTE_STATUS VARCHAR(30);

ALTER TABLE FILES$REFERENCES
ADD DISPUTE_ASSIGNEE VARCHAR(300);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('original_amount_c','all','Oorpronkelijk factuurbedrag','Montant original de la facture','Original invoice amount',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('total_amount_c','all','Totaalbedrag met kosten en interesten','Montant total avec frais et intérêts','Total amount with costs and interests',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('open_amount_c','all','Reeds betaald bedrag','Montant déjà payé','Amount already payed',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('overview_invoices_for_c','all','Overzicht openstaande facturen voor ','Liste des factures ouvertes','Overview of open invoices',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('external_dispute_comment_c','all','Geef hier eventuele opmerkingen of bezwaren in ','Suggerez vos commentaires  ici ','Overview of open invoices',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('add_comment_c','all','Commentaar toevoegen','Ajouter votre commentaire','Add your comment',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('invoice_in_process_c','all','Factuurbetwisting in behandeling','Dispute de la facture est en train d''être procédé','Invoice dispute is being processed',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('dispute_assignee_c','all','Verantwoordelijke','Responsable','Assignee',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('dispute_status_c','all','Betwistingsstatus','Statut de la dispute','Dispute status',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('debtor_dispute_comment_c','all','Opmerking van debiteur','Commentaire du débiteur','Debtor remark',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('no_debtor_dispute_comment_found_c','all','Er werd geen opmerking door de debiteur ingegeven','Le débiteur n''a pas ajouté un commentaire','The debtor has not provided any remarks',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('dispute_comment_c','all','Opmerkingen bij betwisting','Commentaire sur la dispute','Dispute comments',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('invite_for_external_access_c', 'all', 'Uitnodigen voor externe log-in', 'Inviter pour acces externe', 'Invite for external log-in',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('action_code_forAll_c','all','Typ %% voor alle acties.','Tapez %% pour tous les actions.','Type %% for all actions.',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('relationCode_c','all','relatie code','code relation','relation code',0);
