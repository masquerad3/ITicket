USE ITicket;
GO

CREATE OR ALTER PROCEDURE dbo.sp_read_all_tickets
AS
BEGIN
  SET NOCOUNT ON;

  SELECT
    t.ticket_id,
    t.user_id,
    t.subject,
    t.category,
    t.priority,
    t.department,
    t.location,
    t.description,
    t.preferred_contact,
    t.status,
    t.attachments,
    t.assigned_to,
    t.assigned_at,
    t.resolved_at,
    t.created_at,
    t.updated_at,
    u.first_name AS requester_first_name,
    u.last_name  AS requester_last_name,
    a.first_name AS assignee_first_name,
    a.last_name  AS assignee_last_name
  FROM dbo.TICKETS t
  INNER JOIN dbo.USERS u ON u.user_id = t.user_id
  LEFT JOIN dbo.USERS a ON a.user_id = t.assigned_to
  ORDER BY t.created_at DESC;
END
GO
