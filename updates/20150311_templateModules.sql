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
