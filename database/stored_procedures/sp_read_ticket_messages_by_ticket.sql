USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_read_ticket_messages_by_ticket
  @ticket_id INT,
  @include_internal BIT = 0
AS
BEGIN
  SET NOCOUNT ON;

  SELECT
    m.message_id,
    m.ticket_id,
    m.user_id,
    m.message_type,
    m.body,
    m.created_at,
    u.first_name AS user_first_name,
    u.last_name  AS user_last_name,
    u.role       AS user_role
  FROM dbo.TICKET_MESSAGES m
  INNER JOIN dbo.USERS u ON u.user_id = m.user_id
  WHERE m.ticket_id = @ticket_id
    AND (
      @include_internal = 1
      OR ISNULL(m.message_type, 'public') <> 'internal'
    )
  ORDER BY m.created_at ASC;
END
GO
