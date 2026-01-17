USE ITicket;
GO

-- Reads a single ticket message by id (scoped to ticket).
CREATE OR ALTER PROCEDURE dbo.sp_read_ticket_message_by_id
    @ticket_id INT,
    @message_id INT
AS
BEGIN
    SET NOCOUNT ON;

    SELECT TOP 1
        message_id,
        ticket_id,
        user_id,
        body,
        message_type,
        created_at
    FROM dbo.TICKET_MESSAGES
    WHERE ticket_id = @ticket_id
        AND message_id = @message_id;
END
