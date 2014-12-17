UPDATE TEKSTEN SET TEKSTEN_ID='220',CODE='selectplease_c',NAV='all',NL='Selecteer a.u.b. een bestand met het volgende formaat, na het selecteren wordt dit bestand automatisch ingelezen

formaat voorbeeld (Dossier, bedrag, datum,rekening code):
KLNT2;;1024,76;01/12/2012;EXTERNAL;C101
KLNT3;JAARABONNEMENT;102,60;01/12/2012;EXTERNAL;C101',FR='Sélectionnez un fichier au format suivant, après quoi ce fichier est lu automatiquement en

exemple format (Référence,N° de facture, date, code de compte):
KLNT2;;1024,76;01/12/2012;EXTERNAL;C101
KLNT3;JAARABONNEMENT;102,60;01/12/2012;EXTERNAL;C101',EN='Please select the client and file with payments format (Filenumberr, amount, date,account code):
KLNT2;;1024,76;01/12/2012;EXTERNAL;C101
KLNT3;JAARABONNEMENT;102,60;01/12/2012;EXTERNAL;C101' where TEKSTEN_ID='220';

UPDATE TEKSTEN SET TEKSTEN_ID='226',CODE='reference_c',NAV='all',NL='Referentie',FR='Reference',EN='Reference' where TEKSTEN_ID='226';
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN) VALUES ('invoice_reference_c','financial_insert_payments','Factuurnummer','Facture','Invoice')


UPDATE TEKSTEN SET TEKSTEN_ID='1015',CODE='vatnr_c',NAV='all',NL='ond. nr',FR='tva',EN='Vat' where TEKSTEN_ID='1015';


UPDATE TEKSTEN SET TEKSTEN_ID='1227',CODE='factuurnummer_c',NAV='all',NL='Factuurnummer',FR='N° de facture',EN='Invoice N°' where TEKSTEN_ID='1227';


ALTER TABLE TEMP_PAYMENTS ADD INVOICE_REFERENCE DOM_REFERENCE;