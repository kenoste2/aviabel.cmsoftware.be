INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('confirmation_needed_c', 'all','Bevestiging nodig', 'Besoin de confirmation','confirmation needed',0)#
alter table FILES$ACTIONS add CONFIRMATION_NEEDED CHAR(1) DEFAULT 0#
alter table FILES$FILE_ACTIONS add CONFIRMED CHAR(1) DEFAULT 0#
update FILES$FILE_ACTIONS set CONFIRMED = 1#
update FILES$ACTIONS set CONFIRMATION_NEEDED = 0#
create index IDX_CONFIRMED  on FILES$FILE_ACTIONS computed by (CONFIRMED)#
create index IDX_CONFIRMATION_NEEDED  on FILES$ACTIONS computed by (CONFIRMATION_NEEDED)#

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('menu_confirm_view','all','Confirm actions','Confirmer les actions','Confirm actions',0)#
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('confirm_actions_c', 'all','Bevestig deze acties', 'Confirmer actions','confirm these actions',0)#
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('check_all_c', 'all','Selecteer alle acties', 'Sélectionne toutes les opérations','Check all actions',0)#
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('action_added_confirmation_needed_c', 'all','Deze actie werd geregistreerd, maar moet goedgekeurd worden.', 'Cette action a été enregistrée , mais doit être approuvée.','This action was registered , but must be approved.',0)#

CREATE GENERATOR GEN_FILES$AGENDA_ID#

create table FILES$FILE_AGENDA (
FILE_AGENDA_ID DOM_RECORD_ID not null,
FILE_ID DOM_RECORD_ID,
ACTION_ID DOM_RECORD_ID,
REMARKS VARCHAR (255),
ACTION_DATE DOM_DATE,
ACTION_USER DOM_CURRENT_USER,
TEMPLATE_ID DOM_RECORD_ID,
PRINTED CHAR (1),
TEMPLATE_CONTENT BLOB,
EMAIL VARCHAR (255),
ADDRESS VARCHAR(255),
GSM VARCHAR (255),
VIA VARCHAR(255),
CONFIRMED CHAR(1),
CREATION_USER DOM_CURRENT_USER,
CREATION_DATE DOM_DATE
)#

ALTER TABLE FILES$FILE_AGENDA ADD CONSTRAINT PK_FILES$AGENDA PRIMARY KEY (FILE_AGENDA_ID)#

CREATE OR ALTER TRIGGER FILES$AGENDABI FOR FILES$FILE_AGENDA
ACTIVE BEFORE INSERT POSITION 0
AS
BEGIN
  IF (NEW.FILE_AGENDA_ID IS NULL) THEN
    NEW.FILE_AGENDA_ID = GEN_ID(GEN_FILES$AGENDA_ID,1);
END#



create index IDX__ACONFIRMED  on FILES$FILE_AGENDA computed by (CONFIRMED)#
