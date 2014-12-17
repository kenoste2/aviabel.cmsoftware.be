ALTER SEQUENCE GEN_TEKSTEN_ID RESTART WITH 1700;
create trigger teksten_bi2 for teksten
active before insert position 0
as
begin
  if (new.teksten_id is null) then
    new.teksten_id = gen_id(gen_teksten_id,1);
end

DELETE FROM SUPPORT$ZIP_CODES Z WHERE CODE='' AND (SELECT COUNT(*) FROM SYSTEM$COLLECTORS C WHERE C.ZIP_CODE_ID = Z.ZIP_CODE_ID) = 0 AND (SELECT COUNT(*) FROM SYSTEM$USERS D WHERE D.ZIP_CODE_ID = Z.ZIP_CODE_ID) = 0 AND (SELECT COUNT(*) FROM FILES$DEBTORS E WHERE E.ZIP_CODE_ID = Z.ZIP_CODE_ID) = 0
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN) VALUES ('via_c','all','via','via','via');
INSERT INTO TEKSTEN (CODE,NAV,NL,FR,EN) VALUES ('post_c','all','brief','lettre','letter');
