-- ========================================================
-- 1. DATABASE INITIALIZATION
-- ========================================================
DROP DATABASE IF EXISTS TransparaTrack;
CREATE DATABASE TransparaTrack;
USE TransparaTrack;

-- ========================================================
-- 2. TABLE STRUCTURES (DDL)
-- ========================================================

-- TABLE: USERS
CREATE TABLE Users (
  UserID INT AUTO_INCREMENT PRIMARY KEY,
  FullName VARCHAR(150) NOT NULL,
  Username VARCHAR(100) NOT NULL UNIQUE,
  PasswordHash VARCHAR(255) NOT NULL,
  Email VARCHAR(150) UNIQUE NOT NULL,
  ContactNum VARCHAR(20),
  ProfileImagePath VARCHAR(255) NULL DEFAULT NULL,
  reset_token VARCHAR(255) NULL DEFAULT NULL,
  token_expiry VARCHAR(255) NULL DEFAULT NULL, 
  UserRole ENUM('Admin','Staff','Viewer') NOT NULL DEFAULT 'Staff',
  CONSTRAINT chk_rtu_email CHECK (
    (UserRole IN ('Admin','Staff') AND Email LIKE '%@rtu.edu.ph')
    OR (UserRole = 'Viewer')
  )
);

-- TABLE: PROJECTS
CREATE TABLE Projects (
  ProjectID INT AUTO_INCREMENT PRIMARY KEY,
  ProjectName VARCHAR(255) NOT NULL,
  Description TEXT,
  ProjectType VARCHAR(100),
  StartDate DATE NOT NULL,
  EndDate DATE,
  ProjectStatus ENUM('Not Started', 'Ongoing', 'Delayed', 'Completed', 'On Hold', 'Cancelled') 
    NOT NULL DEFAULT 'Not Started',
  ProjectManagerID INT,
  FOREIGN KEY (ProjectManagerID) REFERENCES Users(UserID)
    ON UPDATE CASCADE ON DELETE SET NULL
);

-- TABLE: DEPARTMENTS
CREATE TABLE Departments (
  DeptID INT AUTO_INCREMENT PRIMARY KEY,
  DeptName VARCHAR(100) NOT NULL UNIQUE,
  DeptHead VARCHAR(150),
  ContactNum VARCHAR(20),
  ContactEmail VARCHAR(150)
);

-- TABLE: PROJECTDEPARTMENT
CREATE TABLE ProjectDepartment (
  ProjectID INT NOT NULL,
  DeptID INT NOT NULL,
  Role VARCHAR(100) NOT NULL,
  PRIMARY KEY (ProjectID, DeptID),
  FOREIGN KEY (ProjectID) REFERENCES Projects(ProjectID)
    ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (DeptID) REFERENCES Departments(DeptID)
    ON UPDATE CASCADE ON DELETE CASCADE
);

-- TABLE: BUDGET
CREATE TABLE Budget (
  BudgetID INT AUTO_INCREMENT PRIMARY KEY,
  ProjectID INT NOT NULL,
  AllocatedAmount DECIMAL(15,2) NOT NULL CHECK (AllocatedAmount >= 0),
  FiscalYear YEAR NOT NULL,
  LastUpdated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT UQ_Budget_ProjectID UNIQUE (ProjectID), 
  FOREIGN KEY (ProjectID) REFERENCES Projects(ProjectID)
    ON UPDATE CASCADE ON DELETE CASCADE
);

-- TABLE: AUDIT LOG
CREATE TABLE AuditLog (
  AuditID INT AUTO_INCREMENT PRIMARY KEY,
  ProjectID INT NOT NULL,
  AuditAction VARCHAR(100) NOT NULL,
  PerformedBy INT,
  UserRole VARCHAR(50),
  ActionTimestamp DATETIME DEFAULT CURRENT_TIMESTAMP,
  IPAddress VARCHAR(45) NULL,
  FOREIGN KEY (ProjectID) REFERENCES Projects(ProjectID)
    ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (PerformedBy) REFERENCES Users(UserID)
    ON UPDATE CASCADE ON DELETE SET NULL
);

-- TABLE: EVIDENCE
CREATE TABLE Evidence (
  EvidenceID INT AUTO_INCREMENT PRIMARY KEY,
  ProjectID INT NOT NULL,
  FilePath VARCHAR(255) NOT NULL,
  FileType VARCHAR(50),
  FileSize INT CHECK (FileSize >= 0),
  FileDescription TEXT,
  EvidenceCategory VARCHAR(50),
  UploadedBy INT,
  UploadDate DATETIME DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (ProjectID) REFERENCES Projects(ProjectID)
    ON UPDATE CASCADE ON DELETE CASCADE,
  FOREIGN KEY (UploadedBy) REFERENCES Users(UserID)
    ON UPDATE CASCADE ON DELETE SET NULL
);

-- ========================================================
-- 3. STORED PROCEDURE
-- ========================================================
-- Logic: Atomically creates Project, Budget, and Audit Log in one go.

DROP PROCEDURE IF EXISTS sp_InitializeProject;
DELIMITER //

CREATE PROCEDURE sp_InitializeProject(
    IN p_Name VARCHAR(255), IN p_Desc TEXT, IN p_Type VARCHAR(100),
    IN p_StartDate DATE, IN p_ManagerID INT, IN p_AllocatedAmount DECIMAL(15,2),
    IN p_FiscalYear YEAR
)
BEGIN
    DECLARE new_project_id INT;
    
    INSERT INTO Projects (ProjectName, Description, ProjectType, StartDate, ProjectManagerID, ProjectStatus)
    VALUES (p_Name, p_Desc, p_Type, p_StartDate, p_ManagerID, 'Not Started');
    
    SET new_project_id = LAST_INSERT_ID();
    
    INSERT INTO Budget (ProjectID, AllocatedAmount, FiscalYear)
    VALUES (new_project_id, p_AllocatedAmount, p_FiscalYear);
    
    INSERT INTO AuditLog (ProjectID, AuditAction, PerformedBy, UserRole, ActionTimestamp)
    VALUES (new_project_id, 'Project & Budget Initialization', p_ManagerID, 'Admin', NOW());
END //
DELIMITER ;

-- ========================================================
-- 4. SMART TRIGGERS (AUTOMATED AUDITING)
-- ========================================================
-- These triggers handle the automation while keeping the history clean
-- during the initial project creation.

DROP TRIGGER IF EXISTS trg_AuditProjectInsert;
DROP TRIGGER IF EXISTS trg_AuditProjectUpdate;
DROP TRIGGER IF EXISTS trg_AuditBudgetInsert;
DROP TRIGGER IF EXISTS trg_AuditBudgetUpdate;
DROP TRIGGER IF EXISTS trg_AuditEvidenceInsert;

DELIMITER //

-- TRIGGER 1: PROJECT CREATION (Always Log)
CREATE TRIGGER trg_AuditProjectInsert
AFTER INSERT ON Projects
FOR EACH ROW
BEGIN
    INSERT INTO AuditLog (ProjectID, AuditAction, PerformedBy, UserRole, ActionTimestamp)
    VALUES (
        NEW.ProjectID, 
        'Project Created', 
        IFNULL(@current_user_id, NEW.ProjectManagerID), 
        IFNULL(@current_user_role, 'System Automator'), 
        NOW()
    );
END //

-- TRIGGER 2: PROJECT UPDATES (Status, Name, Dates, Desc)
CREATE TRIGGER trg_AuditProjectUpdate
AFTER UPDATE ON Projects
FOR EACH ROW
BEGIN
    SET @actor_id = IFNULL(@current_user_id, OLD.ProjectManagerID);
    SET @actor_role = IFNULL(@current_user_role, 'System Automator');

    -- Check Name
    IF OLD.ProjectName != NEW.ProjectName THEN
        INSERT INTO AuditLog (ProjectID, AuditAction, PerformedBy, UserRole, ActionTimestamp)
        VALUES (NEW.ProjectID, CONCAT('Renamed from "', OLD.ProjectName, '" to "', NEW.ProjectName, '"'), @actor_id, @actor_role, NOW());
    END IF;

    -- Check Status
    IF OLD.ProjectStatus != NEW.ProjectStatus THEN
        INSERT INTO AuditLog (ProjectID, AuditAction, PerformedBy, UserRole, ActionTimestamp)
        VALUES (NEW.ProjectID, CONCAT('Status changed from ', OLD.ProjectStatus, ' to ', NEW.ProjectStatus), @actor_id, @actor_role, NOW());
    END IF;

    -- Check Start Date
    IF OLD.StartDate != NEW.StartDate THEN
        INSERT INTO AuditLog (ProjectID, AuditAction, PerformedBy, UserRole, ActionTimestamp)
        VALUES (NEW.ProjectID, CONCAT('Start Date changed from ', OLD.StartDate, ' to ', NEW.StartDate), @actor_id, @actor_role, NOW());
    END IF;

    -- Check End Date
    IF IFNULL(OLD.EndDate, '') != IFNULL(NEW.EndDate, '') THEN
        INSERT INTO AuditLog (ProjectID, AuditAction, PerformedBy, UserRole, ActionTimestamp)
        VALUES (NEW.ProjectID, CONCAT('End Date updated to ', IFNULL(NEW.EndDate, 'TBD')), @actor_id, @actor_role, NOW());
    END IF;

    -- Check Description
    IF OLD.Description != NEW.Description THEN
        INSERT INTO AuditLog (ProjectID, AuditAction, PerformedBy, UserRole, ActionTimestamp)
        VALUES (NEW.ProjectID, 'Updated project description', @actor_id, @actor_role, NOW());
    END IF;
END //

-- TRIGGER 3: SMART BUDGET INSERT (Silent on Create, Active Later)
CREATE TRIGGER trg_AuditBudgetInsert
AFTER INSERT ON Budget
FOR EACH ROW
BEGIN
    DECLARE creation_time DATETIME;
    
    -- Check when the project was created
    SELECT ActionTimestamp INTO creation_time FROM AuditLog 
    WHERE ProjectID = NEW.ProjectID AND AuditAction = 'Project Created' 
    ORDER BY ActionTimestamp DESC LIMIT 1;

    -- Only log if project is OLDER than 60 seconds (meaning this is a later update)
    IF (creation_time IS NULL) OR (TIMESTAMPDIFF(SECOND, creation_time, NOW()) > 60) THEN
        INSERT INTO AuditLog (ProjectID, AuditAction, PerformedBy, UserRole, ActionTimestamp)
        VALUES (NEW.ProjectID, CONCAT('Initial budget allocated: ₱', FORMAT(NEW.AllocatedAmount, 2)), IFNULL(@current_user_id, NULL), IFNULL(@current_user_role, 'System Automator'), NOW());
    END IF;
END //

-- TRIGGER 4: BUDGET UPDATES (Always Log)
CREATE TRIGGER trg_AuditBudgetUpdate
AFTER UPDATE ON Budget
FOR EACH ROW
BEGIN
    IF OLD.AllocatedAmount != NEW.AllocatedAmount THEN
        INSERT INTO AuditLog (ProjectID, AuditAction, PerformedBy, UserRole, ActionTimestamp)
        VALUES (
            NEW.ProjectID, 
            CONCAT('Budget adjusted from ₱', FORMAT(OLD.AllocatedAmount, 2), ' to ₱', FORMAT(NEW.AllocatedAmount, 2)), 
            IFNULL(@current_user_id, NULL), 
            IFNULL(@current_user_role, 'System Automator'), 
            NOW()
        );
    END IF;
END //

-- TRIGGER 5: SMART EVIDENCE INSERT (Silent on Create, Active Later)
CREATE TRIGGER trg_AuditEvidenceInsert
AFTER INSERT ON Evidence
FOR EACH ROW
BEGIN
    DECLARE creation_time DATETIME;
    
    -- Check when the project was created
    SELECT ActionTimestamp INTO creation_time FROM AuditLog 
    WHERE ProjectID = NEW.ProjectID AND AuditAction = 'Project Created' 
    ORDER BY ActionTimestamp DESC LIMIT 1;

    -- Only log if project is OLDER than 60 seconds (meaning this is a new file added later)
    IF (creation_time IS NULL) OR (TIMESTAMPDIFF(SECOND, creation_time, NOW()) > 60) THEN
        INSERT INTO AuditLog (ProjectID, AuditAction, PerformedBy, UserRole, ActionTimestamp)
        VALUES (NEW.ProjectID, CONCAT('Uploaded new ', NEW.EvidenceCategory), IFNULL(@current_user_id, NEW.UploadedBy), IFNULL(@current_user_role, 'System Automator'), NOW());
    END IF;
END //

DELIMITER ;