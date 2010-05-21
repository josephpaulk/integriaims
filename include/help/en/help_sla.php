
<h1>SLA management</h1>

Integria enforme four types of SLA, based on three definable parameter per SLA item.

<ul>
<li>Min. Response time (hr): For new incidents (status "new"), is the time Integria wait until mark it as "SLA critical", and notify the incident owner about that.

<li>Max. Response time, not defined a a parameter, is 10 x Min. Resp. Time, and it's the time an incident can wait for a response of a user different who create it (if incident is waiting a response for the creator, incident will not be marked out of SLA).

<li>Max. Resolution time (hr): Max time an incident can be opened.

<li>Max. incidents of this type opened at same time.

</ul>

SLA enformcement email warnings are sent to the OWNER of incident, not the person who opened it.
