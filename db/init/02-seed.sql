-- Jobs Lite seed data. Deterministic: every run produces exactly the same rows.
-- 15 cities, 20 categories, 2,000 companies (50 featured), 50,000 jobs (~90% active).

SET NAMES utf8mb4;
SET SESSION cte_max_recursion_depth = 100000;

INSERT INTO cities (id, name, country) VALUES
(1,'Dubai','United Arab Emirates'),(2,'Abu Dhabi','United Arab Emirates'),(3,'Sharjah','United Arab Emirates'),
(4,'Ajman','United Arab Emirates'),(5,'Ras Al Khaimah','United Arab Emirates'),(6,'Riyadh','Saudi Arabia'),
(7,'Jeddah','Saudi Arabia'),(8,'Dammam','Saudi Arabia'),(9,'Doha','Qatar'),(10,'Kuwait City','Kuwait'),
(11,'Manama','Bahrain'),(12,'Muscat','Oman'),(13,'Cairo','Egypt'),(14,'Amman','Jordan'),(15,'Beirut','Lebanon');

INSERT INTO categories (id, name, slug) VALUES
(1,'Software Engineering','software-engineering'),(2,'Data & Analytics','data-analytics'),
(3,'Product Management','product-management'),(4,'Design','design'),(5,'Marketing','marketing'),
(6,'Sales','sales'),(7,'Finance & Accounting','finance-accounting'),(8,'Human Resources','human-resources'),
(9,'Operations','operations'),(10,'Customer Service','customer-service'),
(11,'Civil & Mechanical Engineering','civil-mechanical-engineering'),(12,'Healthcare','healthcare'),
(13,'Education','education'),(14,'Legal','legal'),(15,'Hospitality','hospitality'),
(16,'Supply Chain & Logistics','supply-chain-logistics'),(17,'Real Estate','real-estate'),
(18,'Banking','banking'),(19,'Administration','administration'),(20,'IT Infrastructure','it-infrastructure');

-- 2,000 companies with unique invented names (20 x 25 x 4 combinations)
INSERT INTO companies (id, name, industry, is_featured)
WITH RECURSIVE seq (n) AS (SELECT 0 UNION ALL SELECT n + 1 FROM seq WHERE n < 1999)
SELECT
    n + 1,
    CONCAT(
        ELT(1 + ((n + FLOOR(n / 20)) % 20), 'Al Noor','Blue Dune','Crescent','Desert Rose','Emerald','Falcon','Golden Sands','Horizon',
            'Iris','Jasmine','Lumen','Marina','Nova Star','Oasis','Pearl','Qamar','Saffron','Tamarind','Unity','Zenith'),
        ' ',
        ELT(1 + (FLOOR(n / 20) % 25), 'Technologies','Logistics','Healthcare','Trading','Consulting','Hospitality','Energy',
            'Finance','Retail','Media','Properties','Education','Engineering','Foods','Capital','Systems','Labs','Motors',
            'Telecom','Pharma','Aviation','Marine','Security','Analytics','Ventures'),
        ' ',
        ELT(1 + (FLOOR(n / 500) % 4), 'Group','LLC','Holdings','Solutions')
    ),
    ELT(1 + ((n * 7 + FLOOR(n / 40)) % 10), 'Technology','Logistics','Healthcare','Retail','Finance','Hospitality','Energy',
        'Real Estate','Education','Manufacturing'),
    IF((n + 1) % 40 = 0, 1, 0)
FROM seq;

-- 50,000 jobs
INSERT INTO jobs (id, title, company_id, category_id, city_id, salary_min, salary_max, currency,
                  employment_type, experience_min, description, posted_at, is_active)
WITH RECURSIVE seq (n) AS (SELECT 1 UNION ALL SELECT n + 1 FROM seq WHERE n < 50000)
SELECT
    n,
    TRIM(CONCAT(ELT(1 + (n % 5), 'Senior', 'Junior', 'Lead', '', 'Principal'), ' ', role_name)),
    company_id,
    category_id,
    city_id,
    salary_min,
    salary_min + 3000 + ((n * 31) % 10) * 1000,
    'AED',
    ELT(1 + (n % 10), 'full_time','full_time','contract','full_time','part_time','full_time','full_time',
        'contract','full_time','full_time'),
    (n * 3) % 11,
    CONCAT('We are looking for a ', role_name, ' to join a growing team. ',
           'You will work closely with colleagues across the business, own your deliverables end to end, ',
           'and help us serve our customers better. Experience in a similar role is an advantage.'),
    TIMESTAMP('2026-11-15 12:00:00') - INTERVAL ((n * 7919) % 525600) MINUTE,
    IF(n % 10 = 0, 0, 1)
FROM (
    SELECT
        n,
        role_idx,
        ELT(role_idx, 'Backend Developer','Frontend Developer','Full-Stack Developer','Mobile Developer',
            'Data Analyst','Data Engineer','Product Manager','UX Designer','Graphic Designer',
            'Digital Marketing Specialist','Content Writer','Account Manager','Sales Executive','Accountant',
            'Financial Analyst','HR Business Partner','Recruiter','Operations Manager','Customer Support Agent',
            'Civil Engineer','Mechanical Engineer','Registered Nurse','Math Teacher','Legal Counsel',
            'Hotel Manager','Logistics Coordinator','Property Consultant','Relationship Manager',
            'Office Administrator','Network Engineer') AS role_name,
        ELT(role_idx, 1,1,1,1,2,2,3,4,4,5,5,6,6,7,7,8,8,9,10,11,11,12,13,14,15,16,17,18,19,20) AS category_id,
        1 + (CRC32(n) % 2000) AS company_id,
        1 + ((n * 11 + FLOOR(n / 7)) % 15) AS city_id,
        5000 + ((n * 97) % 40) * 1000 AS salary_min
    FROM (SELECT n, 1 + ((n * 7) % 30) AS role_idx FROM seq) s
) t;

-- Refresh optimizer statistics after the bulk load, as a production database would have.
-- (This adds no indexes; it only makes sure any index you add is actually considered by the optimizer.)
ANALYZE TABLE cities, categories, companies, jobs, applications;
