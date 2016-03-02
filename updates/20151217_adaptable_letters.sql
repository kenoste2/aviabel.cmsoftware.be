INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('content_adabtable_c', 'all','Inhoud wijzigbaar bij toevoegen actie', 'Le contenu est modifiable','Content can be changed when adding an action',0);
alter table system$templates add ADAPTABLE CHAR(1) DEFAULT 0;
update system$templates set ADAPTABLE = 0;


