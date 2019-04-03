<?php

/*
SELECT * FROM applicant a, applicationprocess p WHERE a.ApplicantId = p.ApplicantId AND AcademicYear = 2018 AND p.ProcessStepId = 2 GROUP BY a.ApplicantId;
SELECT * FROM applicant a, applicantscholarship s WHERE a.ApplicantId = s.ApplicantId AND a.AcademicYear = 2018 AND s.AcademicYear = 2018 AND a.ApplicantId IN (SELECT DISTINCT a.ApplicantId FROM applicant a, applicationprocess p WHERE a.ApplicantId = p.ApplicantId AND AcademicYear = 2018 AND p.ProcessStepId = 2) GROUP BY a.ApplicantId;
SELECT SUM(s.AmountAwarded) FROM applicant a, applicantscholarship s WHERE a.ApplicantId = s.ApplicantId AND a.AcademicYear = 2018 AND s.AcademicYear = 2018;

SELECT DISTINCT s.ApplicantId FROM applicantscholarship s WHERE s.AcademicYear = 2018;

SELECT n.DirectNeed, n.IndirectNeed, a.* FROM applicant a, studentneed n WHERE a.ApplicantId = n.ApplicantId AND a.AcademicYear = 2018 AND n.DirectNeed > 0 AND a.ApplicantId NOT IN (SELECT DISTINCT s.ApplicantId FROM applicantscholarship s WHERE s.AcademicYear = 2018);
*/