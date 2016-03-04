<h1>Condiciones de reglas de flujo de trabajo</h1>

<p>
As general rule, workflow rules will only fired ONCE. So you set a rule to change, for example the user if user = "xxx" and group = "zzz" and later, someone, manually change again the user to "xxx", rule will not fired, because it matches that ticket before. This prevents problems on workflow definitions, which could be harmful on some conditions.
</p>
<p>
The only exception to this behaviour is where condition is update time. If you set a rule to fire when the ticket is more than X time without an update, it will create automatically a default action to "update the ticket". This will avoid rule to fire again and again.
</p>
<p>
This is a typical case for that kind of condition:
<br>
<br>
“I need to send an email to a coordinator, when a ticket with high priority on a specific group has more than 5 days without any update"
<br>
<br>
Create a condition with "Match all fields", specific group, high priority and in time update choose 5 days
</p>

<p>
<?php print_image("images/help/workflow_conditions.png", false); ?>
</p>

<p>
By adding the act of sending an email , the action Refresh the ticket, we'll leave as it is created automatically , paragraph Update ticket and prevent further jumping Rule.
</p>

<p>
<?php print_image("images/help/workflow_actions.png", false, false); ?>
</p>
<p>
<?php print_image("images/help/workflow_actions2.png", false, false); ?>
</p>
<p>
To spend a week, if you have not updated the incidence, it will skip the rule and so indefinitely.
</p>

