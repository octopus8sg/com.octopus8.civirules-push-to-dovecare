<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-generic_date">
  <div class="help-block" id="help">
    {ts}You can test an activity or participation date against specific dates or date fields (Comparison Date or From and To Date depending on the Operator).{/ts}
    <br /><br />
    {ts}You can also select to test against either the date the rule is triggered or the date the action is executed. If you do not use delayed actions this is the same date but if you do use a delay there is a diffderence!{/ts}
    <br />
    {ts}For example, if the rule is triggered by a new activity on the 1 April but the action is executed with a delay of 1 day, comparing with the date the rule is triggered will compare with 1 April whilst comparing with the date the action is executed will compare with 2 April (if you did NOT check the <em>Don't recheck condition upon processing of delayed action!</em> box when defining the delay){/ts}
    <br />
    {ts}Please note that using the date the action is executed only makes sense if you also specify a delay!!!!{/ts}
  </div>

  <div class="crm-section sector-section date-select">
    <div class="label">{$form.date_select.label}</div>
    <div class="content type">{$form.date_select.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section">
    <div class="label">{$form.operator.label}</div>
    <div class="content operator">{$form.operator.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section sector-section activity-compare-type">
    <div class="label">{$form.compare_type.label}</div>
    <div class="content type">{$form.compare_type.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section sector-section activity-compare-date">
    <div class="label">{$form.activity_compare_date.label}</div>
    <div class="content activity-date-comparison">{$form.activity_compare_date.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section sector-section activity-compare-field">
    <div class="label">{$form.activity_compare_field.label}</div>
    <div class="content activity-field-comparison">{$form.activity_compare_field.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section sector-section empty-field">
    <div class="label">{$form.empty_field.label}</div>
    <div class="content empty-field">{$form.empty_field.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section sector-section activity-from-date">
    <div class="label">{$form.activity_from_date.label}</div>
    <div class="content activity-date-from">{$form.activity_from_date.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section sector-section activity-to-date">
    <div class="label">{$form.activity_to_date.label}</div>
    <div class="content activity-date-to">{$form.activity_to_date.html}</div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
  {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>
{literal}
  <script type="text/javascript">
    (function(CRM, $){
      $(function(){

        var $fields = $('#operator, [name="compare_type"]');       
        $fields.change(function(){ fieldDisplay(); });
        fieldDisplay();

        function fieldDisplay() {

          var selectedOperator = $('#operator').find(":selected").text();
          var compareType = $('[name="compare_type"]:checked').val();

          if (selectedOperator === 'between') { // automatically fixed
            $('.activity-compare-type').hide();
            $('.activity-compare-date').hide();
            $('.activity-compare-field').hide();
            $('.empty-field').hide();
            $('.activity-from-date').show();
            $('.activity-to-date').show();
          } else {
            $('.activity-from-date').hide();
            $('.activity-to-date').hide();
            $('.activity-compare-type').show();
            $('.activity-compare-date').toggle(compareType==='fixed');
            $('.activity-compare-field').toggle(compareType==='field');
            $('.empty-field').toggle(compareType==='fixed' || compareType==='field');
          }
        }

      });
    })(CRM, CRM.$);

  </script>
{/literal}