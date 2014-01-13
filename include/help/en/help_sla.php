
<h1>SLA management</h1>
SLA is the way to "check" if the ticket management works right. SLA are processed periorically in Integria, using the scheduled task programmed when you install Integria.
<br>
The SLA is processed with several parameters:
<br><ul>
<li>    Name: Is the text that will appear in the selection combo to identify the SLA. 

   <li> Enforced: It does that the SLA send the emails where it's not fulfilled (enforced) or that only notify with a light sign. 

<li>    Min. Response Time: Shows in hours the response time you should observe between for one notification (new ticket or WU) of the ticket creator. After this time, the SLA will be fired. For example, if it's for 4 hours, and a new ticket has been for 4.1 hours, the SLA will be fired. For example, if it's an old ticket (1 week) and the last WU is from the ticket creator and it has more than 4 hours, it will also fire the ticket. 

<li>    Max. Resolution time: Shows in hours, the maximum life time of one ticket. If a ticket is older that this and it's not closed or solved, then the SLA will be fired. 

<li>    Max. Inactivity time: Shows in hours, the maximum time an ticket could be without update. 

<li>    Max.Nº of tickets simultaneously opened: Shows the total nº of tickets that could be simultaneously opened. If there are more, then the SLA will be fired. 

<li>    Base SLA : Shows the the SLA is related with another (only in at informative level) 

   <li> Firing only at midweek not the weekends. 

<li>    Starting time to activate SLA:Time from which the SLA beggins to calculate(e.g:9 a.m). 

<li>    End time for SLA:Time from the SLA will be not not calculated (e.g:18 hr). 

<li>    Description: Informative text to describe the SLA. 

<li>    Max. ticket inactivity: The maximum time without activity. After this time Integria will send an email to the ticket responsible to remind him that the ticket is open. 
</ul>
<h2>
What "The SLA will be fired" means ?</h2>

It means that the system will send an notification by email to the ticket owner, notifying that a ticket doesn't fulfill the requisites fixed in the SLA to the ticket implicated. One ticket could be subject to different SLA simultaneously. If it is associated to different inventory objets, and these inventory objects are linked to different contracts, and these contracts are subject to different SLAs. 
