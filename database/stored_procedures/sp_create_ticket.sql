USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_create_ticket
  @user_id            INT,
  @subject            NVARCHAR(255),
  @category           NVARCHAR(50),
  @priority           NVARCHAR(10),
  @department         NVARCHAR(100) = NULL,
  @location           NVARCHAR(100) = NULL,
  @description        NVARCHAR(MAX),
  @preferred_contact  NVARCHAR(10),
  @status             NVARCHAR(20) = 'open'
AS
BEGIN
  SET NOCOUNT ON;

  INSERT INTO dbo.TICKETS
    (user_id, subject, category, priority, department, location, description, preferred_contact, status, attachments, created_at, updated_at)
  VALUES
    (@user_id, @subject, @category, @priority, @department, @location, @description, @preferred_contact, @status, NULL, SYSDATETIME(), SYSDATETIME());

  SELECT CAST(SCOPE_IDENTITY() AS INT) AS ticket_id;
END
GO
