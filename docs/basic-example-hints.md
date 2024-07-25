# Hints on writing your own rules

Hopefully the examples here have given you enough clues so you can start writing your own rules.  This page provides a few more tips.

## Triggers

Triggers are the starting point, the thing that causes CiviRules to wake up.  There are two types: those that run immediately based on a _change_ and those that run according to a _schedule_. 

The first thing to decide is what the right starting point is for your rule.

In some situations, the trigger is obvious: you want to do something based on a phone call activity being created, so your trigger will be one of the Activity ones, and in this case the 'is added' one.

In more complex situations, such as a membership being created, there are multiple different changes occuring - a membership, an activity, possibly a contribution.  Although these appear to happen at the same time, there is a sequence and your rule will trigger as soon as that entity change takes place - which may be before the other related entity changes. You may need to experiment a bit to find the best trigger for your needs.

Sometimes an entity is created and then immediately changed so although it might seem that you want an "is added" trigger, you may need to use an "is changed" trigger instead.

Occasionally changes are made to the database in ways that bypass the mechanism used by CiviRules to insert itself into the action (technicaly the 'civicrm_post' hook).

Scheduled rules (also known as "cron triggers") come in two flavours:

- Date comparisons: such as "Individual has birthday" - no change has occured within the system but a condition (current date matches day/month of date of birth field) is true. Some of the date-based conditions accept configuration parameters to trigger some time period before or after the date.
- Daily checks: see below

!!! tip
    Start by choosing an appropriate trigger and a simple action (like adding a tag) and make sure that the events you want to respond to are included.  Conditions can be added to eliminate events you are not interested in, but they will only reduce the scope of your rule, not increase it.

Most triggers are quite simple and their function is obvious from the name, but a few deserve more explanation:

### Scheduled Reminder log is added/changed

When scheduled reminders run, a log is created recording the result.  This trigger can be used to invoke rules based on the results of a scheduled reminder run.

Note that Scheduled Reminder logs are created in such a way that 'is added' is never triggered currently - that might change in future.  However, the 'is changed' rule is the more useful trigger since it is invoked when the result of a scheduled job is recorded.

### Daily trigger for group members

This allows conditions to be checked each day for members of a specified group.

### Daily trigger for case activity

This allows conditions to be checked each day for cases.  For example, this trigger combined with the "Days since last case activity" could be used to take action when cases start to go stale.

### Contact is added to Group

This is triggered when a form is submitted that includes the group data for the contact, which may result in the rule being triggered even when the contact was not actually added to the group, but simply continued to be in the group. For example, if you save the Edit Contact screen and the contact is a member of the group in question, even if you aren't adding them to the group, the rule will be triggered.

## Conditions

Conditions limit the scope of the trigger by specifying additional criteria that must be satisfied.

The available conditions depend on the trigger and the entities it involves.

Most conditions are fairly obvious from the name.

In the example above of responding to a new Phone call Activity, we selected the 'Activity is added' trigger - but that is activated for _all_ activities that are created.  Using Conditions we limit that using the 'Activity is (not) one of Type(s)' condition to narrow our scope to just those of type 'Phone call'

!!! tip
    Add enough conditions to pinpoint the situations of interest.  For example, do you care what status your new Phone Call Activity has?  Do you want to take the same action regardless of whether it is Scheduled or Compeleted?  What about Cancelled?  Check your conditions are right by performing tests that _should not_ result in an action as well as those that _should_.

### Field Value Comparison

This powerful conditiion lets you check the value of a field in the entities related to the trigger, including the custom data.  For example, (using the standard dummy data) this condition can be used to check that the custom field 'Most important issue' is set to 'Education'

If there is no specific condition for your situation, Field Value Comparison may provide what you need.  See the more detailed explanation [here](./basic-example-field-value-comparison-condition.md).

## Actions

Once the trigger has been invoked and the conditions satisfied, the remaining step is for the rule to do something. The actions are fairly obvious from their names.

There are comparatively few Actions compared to the number of Triggers and Conditions.  There are additional extensions that provide more actions such as the "Email API" and "PDF API".  If you want to create your own actions see the developer section of this documentation.

