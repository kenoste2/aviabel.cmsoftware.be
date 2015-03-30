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
)

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('debtor_score_c','all','Score','Score','Score',0);


