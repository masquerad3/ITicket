USE ITicket;
GO

-- Deletes a message by id (scoped to ticket). Authorization is enforced in Laravel.
CREATE OR ALTER PROCEDURE dbo.sp_delete_ticket_message
    @ticket_id INT,
    @message_id INT
AS
BEGIN
    SET NOCOUNT ON;

    DELETE FROM dbo.TICKET_MESSAGES
    WHERE ticket_id = @ticket_id
        AND message_id = @message_id;
END
