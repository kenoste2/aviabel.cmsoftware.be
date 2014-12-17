SELECT S.tva AS CpyVat,S.nom AS CpyNom,S.adresse AS CpyStraat,S.ville AS CpyCity, S.cp AS CpyZip, S.forme AS CpyJur,S.constitution AS StartDate, C.limit AS credit, C.scoring AS scoring, K.fte AS CpyPersoneel, K.turnover AS CpyOmzet, L.is_bankruptcy AS BankruptyFlag, S.nace AS NaceCode
FROM signa S
LEFT JOIN cache_credit_advice C ON S.tva = C.tva
LEFT JOIN key_figures_last K ON K.tva = S.tva
LEFT JOIN cache_legal_status L ON L.tva = S.tva
LIMIT 100