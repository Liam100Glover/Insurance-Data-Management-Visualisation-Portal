-- Create database
CREATE DATABASE IF NOT EXISTS DBInsurance;
USE DBInsurance;

-- Create driverDetails table
CREATE TABLE driverDetails (
    ID INT PRIMARY KEY,
    KIDSDRIV INT,
    Age INT,
    INCOME VARCHAR(255),
    MSTATUS BOOLEAN,
    GENDER VARCHAR(50),
    EDUCATION VARCHAR(255),
    OCCUPATION VARCHAR(255)
);

-- Create carDetails table
CREATE TABLE carDetails (
    carID INT PRIMARY KEY,
    ID INT,
    CAR_TYPE VARCHAR(255),
    RED_CAR BOOLEAN,
    CAR_AGE INT,
    FOREIGN KEY (ID) REFERENCES driverDetails(ID)
);

-- Create claimDetails table
CREATE TABLE claimDetails (
    claimID INT PRIMARY KEY,
    carID INT,
    ID INT,
    CLAIM_FLAG BOOLEAN,
    CLM_AMT INT,
    CLM_FREQ INT,
    OLDCLAIM INT,
    FOREIGN KEY (carID) REFERENCES carDetails(carID),
    FOREIGN KEY (ID) REFERENCES driverDetails(ID)
);
