<!DOCTYPE html>
<html lang="en">
<body style="margin:0;padding:0;background-color:#0b0f1a;color:#e5e9f2;font-family:'Rajdhani',Arial,sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#0b0f1a;padding:24px 16px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" role="presentation" style="background-color:#131a2a;border-radius:16px;border:1px solid #2a3550;overflow:hidden;">
                    <tr>
                        <td style="padding:24px;background:linear-gradient(135deg,#41d7ff,#8b5cf6);color:#05070e;text-align:center;font-size:20px;font-weight:700;letter-spacing:0.08em;">
                            CRAZY SYSTEMS | HELPDESK
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px 32px;color:#e5e9f2;">
                            <p style="margin:0 0 12px;font-size:18px;">Hello {!!$ticket_agent_name!!},</p>
                            <p style="margin:0 0 16px;color:#9aa6bd;">A reply was added to ticket <strong>#{!!$ticket_number!!}</strong>.</p>
                            <div style="background:#1c2538;border-radius:12px;padding:16px;margin-bottom:16px;border:1px solid #2a3550;">
                                <p style="margin:0 0 8px;color:#e5e9f2;"><strong>From:</strong> {!!$ticket_client_name!!}</p>
                                <p style="margin:0 0 8px;color:#9aa6bd;">{!!$ticket_client_email!!}</p>
                                <div style="margin-top:12px;color:#e5e9f2;">{!!$content!!}</div>
                            </div>
                            <p style="margin:0;color:#9aa6bd;">Kind Regards,</p>
                            <p style="margin:0;font-weight:600;">{!!$system_from!!}</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
