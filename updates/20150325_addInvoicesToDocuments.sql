ALTER TABLE FILE_DOCUMENTS
ADD REFERENCE_ID DOM_RECORD_ID;

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('related_documents_c','all','Gerelateerde documenten','Documents relatés','Related documents',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('document_name_c','all','Naam','Nom','Name',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('document_description_c','all','Beschrijving','Déscription','Description',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('no_documents_found_c','all','Geen documenten gevonden.','Pas de documents retrouvés.','No documents found.',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('document_c','all','Document','Document','Document',0);


