USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_read_unassigned_tickets
AS
BEGIN
  SET NOCOUNT ON;

  SELECT
    t.ticket_id,
    t.user_id,
    t.subject,
    t.category,
    t.priority,
    t.description,
    t.status,
    t.assigned_to,
    t.created_at,
    t.updated_at,
    a.first_name AS assignee_first_name,
    a.last_name  AS assignee_last_name
  FROM dbo.TICKETS t
  LEFT JOIN dbo.USERS a ON a.user_id = t.assigned_to
  WHERE t.assigned_to IS NULL
  ORDER BY t.created_at DESC;
END
GO
