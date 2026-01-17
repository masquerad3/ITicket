USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_hard_delete_ticket
  @ticket_id INT
AS
BEGIN
  SET NOCOUNT ON;

  BEGIN TRY
    BEGIN TRAN;

    DELETE FROM dbo.TICKET_ATTACHMENTS WHERE ticket_id = @ticket_id;
    DELETE FROM dbo.TICKET_TAGS WHERE ticket_id = @ticket_id;
    DELETE FROM dbo.TICKET_MESSAGES WHERE ticket_id = @ticket_id;
    DELETE FROM dbo.TICKETS WHERE ticket_id = @ticket_id;

    COMMIT TRAN;

    SELECT 1 AS ok;
  END TRY
  BEGIN CATCH
    IF @@TRANCOUNT > 0 ROLLBACK TRAN;

    DECLARE @msg NVARCHAR(4000) = ERROR_MESSAGE();
    THROW 50050, @msg, 1;
  END CATCH
END
GO
