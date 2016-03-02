ALTER TABLE FILES$REFERENCES ADD DISPUTE_DATE DATE;
create index ix_reference_dispute_date on FILES$REFERENCES computed by (DISPUTE_DATE);
ALTER TABLE FILES$REFERENCES ADD DISPUTE_ENDED_DATE DATE;
create index ix_reference_dispute_ended_date on FILES$REFERENCES computed by (DISPUTE_ENDED_DATE);

INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('invoice_nr_not_found_c', 'all', 'Factuurnummer werd niet teruggevonden op lijn xVARx. Lijn werd niet geïmporteerd.', 'Le numéro de facture n''est pas retrouvé sur la ligne xVARx. La ligne n''a pas éte importée', 'The invoice number was not found on line xVARx. The line was not imported', 0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('due_date_nr_not_found_c', 'all', 'Vervaldatum niet teruggevonden op lijn xVARx. Een standaardwaarde van 30 dagen in de toekomst werd aangenomen.', 'Date d''écheance n''est pas retrouvée sur la ligne xVARx. La ligne n''a pas été importée', 'The due date was not found on line xVARx. The line was not imported', 0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('due_date_not_in_correct_format_c', 'all', 'Vervaldatum is niet in een geldig formaat op lijn xVARx.', 'Date d''écheance n''est pas formatée correctement sur la ligne xVARx.', 'The due date was not in a correct format on line xVARx.', 0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('ended_date_not_in_correct_format_c', 'all', 'Datum einde betwisting niet in een geldig formaat op lijn xVARx.', 'Date fin de la dispute n''est pas formatée correctement sur la ligne xVARx.', 'End of dispute date was not in a correct format on line xVARx.', 0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('column_layout_c', 'all', 'De plaatsing van de kolommen in het CSV-bestand is als volgt: xVARx.', 'Le placement des colonnes dans le fichier CSV est ainsi: xVARx.', 'Column placement in the CSV file is as follows: xVARx.', 0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('invoice_number_c', 'all', 'Faktuurnummer', 'Numéro de facture', 'Invoice number', 0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('invoice_number_c', 'all', 'Vervaldag', 'Date d''écheance', 'Due date', 0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('ended_date_c', 'all', 'Datum einde betwisting', 'Date fin de la dispute', 'End of dispute date', 0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('dispute_ended_date_c', 'all', 'Datum einde betwisting', 'Date fin de la dispute', 'End of dispute date', 0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('menu_disputed-import_read-csv','all','Inlezen betwistingen','Télécharger les litiges','Upload disputes',0);
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN,SETTINGS) VALUES ('dispute_date_c', 'all', 'Datum betwisting', 'Date de la dispute', 'dispute date', 0);