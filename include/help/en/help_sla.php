
<h1>SLA management</h1>
SLA is the way to "check" if the incident management works right. SLA are processed periorically in Integria, using the scheduled task programmed when you install Integria.
<br>
The SLA is processed with several parameters:
<br><ul>
<li>    Name: Is the text that will appear in the selection combo to identify the SLA. 

   <li> Enforced: It does that the SLA send the emails where it's not fulfilled (enforced) or that only notify with a light sign. 

<li>    Min. Response Time: Shows in hours the response time you should observe between for one notification (new incident or WU) of the incident creator. After this time, the SLA will be fired. For example, if it's for 4 hours, and a new incident has been for 4.1 hours, the SLA will be fired. For example, if it's an old incident (1 week) and the last WU is from the incident creator and it has more than 4 hours, it will also fire the incident. 

<li>    Max. Resolution time: Shows in hours, the maximum life time of one incident. If an incident is older that this and it's not closed or solved, then the SLA will be fired. 

<li>    Max. Inactivity time: Shows in hours, the maximum time an incident could be without update. 

<li>    Max.Nº of incidents simultaneously opened: Shows the total nº of incidents that could be simultaneously opened. If there are more, then the SLA will be fired. 

<li>    Base SLA : Shows the the SLA is related with another (only in at informative level) 

   <li> Firing only at midweek not the weekends. 

<li>    Starting time to activate SLA:Time from which the SLA beggins to calculate(e.g:9 a.m). 

<li>    End time for SLA:Time from the SLA will be not not calculated (e.g:18 hr). 

<li>    Description: Informative text to describe the SLA. 

<li>    Max. incident inactivity: The maximum time without activity. After this time Integria will send an email to the incident responsible to remind him that the incident is open. 
</ul>
<h2>
What "The SLA will be fired" means ?</h2>

It means that the system will send an notification by email to the incident owner, notifying that an incident doesn't fulfill the requisites fixed in the SLA to the incident implicated. One incident could be subject to different SLA simultaneously. If it is associated to different inventory objets, and these inventory objects are linked to different contracts, and these contracts are subject to different SLAs. 
