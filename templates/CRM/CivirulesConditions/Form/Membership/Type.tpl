<h3>{$ruleConditionHeader}</h3>
<div class="crm-block crm-form-block crm-civirule-rule_condition-block-membership_type">
  <div class="crm-section">
    <div class="label">{$form.operator.label}</div>
    <div class="content">{$form.operator.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section membership_type_id">
    <div class="label">{$form.membership_type_id.label}</div>
    <div class="content">{$form.membership_type_id.html}</div>
    <div class="clear"></div>
  </div>
  <div class="crm-section membership_type_ids">
    <div class="label">{$form.membership_type_ids.label}</div>
    <div class="content">{$form.membership_type_ids.html}</div>
    <div class="clear"></div>
  </div>
</div>
<div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
</div>

{literal}
<script type="text/javascript">
  cj(function() {
    cj('#operator').change(function() {
      var operator = cj('#operator').val();
      if ((operator == 2) || (operator == 3)) {
        cj('.crm-section.membership_type_id').addClass('hiddenElement');
        cj('.crm-section.membership_type_ids').removeClass('hiddenElement');
      } else {
        cj('.crm-section.membership_type_id').removeClass('hiddenElement');
        cj('.crm-section.membership_type_ids').addClass('hiddenElement');
      }
    });

    cj('#operator').trigger('change');
  });
</script>
{/literal}
