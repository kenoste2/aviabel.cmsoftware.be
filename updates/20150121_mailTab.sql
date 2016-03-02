CREATE TABLE IMPORTED_MAILS (
  IMPORTED_MAIL_ID int not null primary key,
  FILE_ID int not null references FILES$FILES,
  CREATION_DATE TIMESTAMP,
  FROM_EMAIL varchar(50),
  TO_EMAIL varchar(50),
  MAIL_BODY varchar(10000),
  MAIL_SUBJECT varchar(3000)
);

CREATE TABLE IMPORTED_MAIL_ATTACHMENTS (
   IMPORTED_MAIL_ATTACHMENT_ID int not null primary key,
   IMPORTED_MAIL_ID int not null references IMPORTED_MAILS,
   ORIGINAL_FILENAME varchar(300),
   SERVER_FILENAME varchar(300),
   MIME_TYPE varchar(100),
   CREATION_DATE timestamp
);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('mail_from_c','all','Van','De','From',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('mail_to_c','all','Aan','À','To',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('imported_mails_c','all','E-mails','Emails','Emails',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('no_mails_found_c','all','Geen e-mails gevonden.','Pas d''emails retrouvés','No emails found.',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('mail_subject_c','all','Onderwerp','Sujet','Subject',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('menu_imported-mail_overview','all','E-mails','Emails','Emails',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('date_from_c','all','van','de','from',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('date_till_c','all','tot', 'à','until',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('attachments_c','all','Bijlagen', 'Fichiers joints','Attachments',0);

create index IDX_FROM_EMAIL on IMPORTED_MAILS computed by (FROM_EMAIL)#
create index IDX_MAILCREATED on IMPORTED_MAILS computed by (CREATION_DATE)#
create index IDX_MAILFILEID on IMPORTED_MAILS computed by (FILE_ID)#

