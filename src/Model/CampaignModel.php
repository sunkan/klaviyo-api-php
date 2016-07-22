<?php

namespace Klaviyo\Model;

/**
 * Simple model that represents a campaign.
 */
class CampaignModel extends BaseModel {

  protected $id;
  protected $name;
  protected $subject;
  protected $fromEmail;
  protected $fromName;
  protected $lists;
  protected $template;
  protected $status;
  protected $statusId;
  protected $statusLabel;
  protected $sentAt;
  protected $sendTime;
  protected $created;
  protected $updated;
  protected $numRecipients;
  protected $isSegmented;
  protected $campaignType;
  protected static $optionalDefaults = [
    'id' => '',
    'status' => 'draft',
    'status_id' => 2,
    'status_label' => 'Draft',
    'send_time' => '',
    'updated' => '',
    'num_recipients' => 0,
    'is_segmented' => FALSE,
    'campaign_type' => 'Regular',
  ];

  /**
   * {@inheritdoc}
   */
  public function __construct($configuration) {
    parent::__construct($configuration);

    $this->id = $configuration['id'];
    $this->name = $configuration['name'];
    $this->subject = $configuration['subject'];
    $this->fromEmail = $configuration['from_email'];
    $this->fromName = $configuration['from_name'];
    $this->template = TemplateModel::create($configuration['template']);
    $this->status = $configuration['status'];
    $this->statusId = $configuration['status_id'];
    $this->statusLabel = $configuration['status_label'];
    $this->sentAt = $configuration['sent_at'];
    $this->sendTime = $configuration['send_time'];
    $this->created = new \DateTime($configuration['created']);
    $this->updated = new \DateTime($configuration['updated']);
    $this->numRecipients = $configuration['num_recipients'];
    $this->isSegmented = $configuration['is_segmented'];
    $this->campaignType = $configuration['campaign_type'];

    $this->loadLists($configuration['lists']);
  }

  /**
   * Load the lists associated with the campaign.
   *
   * @param array $lists
   *   An array of list data for which to load the list models.
   *
   * @return $this
   */
  protected function loadLists($lists) {
    $this->lists = [];

    foreach ($lists as $list) {
      $this->lists[] = ListModel::create($list);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function jsonSerialize() {
    return parent::jsonSerialize() + [
      'id' => $this->id,
      'name' => $this->name,
      'subject' => $this->subject,
      'from_email' => $this->fromEmail,
      'from_name' => $this->fromName,
      'lists' => $this->lists,
      'template' => $this->template,
      'status' => $this->status,
      'status_id' => $this->statusId,
      'status_label' => $this->statusLabel,
      'sent_at' => $this->sentAt,
      'send_time' => $this->sendTime,
      'created' => $this->created->format($this->dateFormat),
      'updated' => $this->created->format($this->dateFormat),
      'num_recipients' => $this->numRecipients,
      'is_segmented' => $this->isSegmented,
      'campaign_type' => $this->campaignType,
    ];
  }

}
