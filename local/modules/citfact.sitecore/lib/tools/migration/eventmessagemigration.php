<?

namespace Citfact\SiteCore\Tools\Migration;

class EventMessageMigration extends BaseMigration
{
    private $settings = [];

    public function __construct($settings)
    {
        $this->settings = $settings;
        parent::__construct();
    }

    public function run()
    {
        foreach ($this->settings as $eventName => $eventMessagesData) {
            $this->setEventMessages($eventName, $eventMessagesData);
        }
    }

    public function setEventMessages($eventName, $eventMessagesData)
    {
        $eventMessage = new \CEventMessage();
        $res = $eventMessage->GetList($by = 'ID', $order = 'ASC', ['EVENT_NAME' => $eventName]);
        $items = [];
        while ($item = $res->Fetch()) {
            $items[] = $item;
        }
        if (!$items) {
            $this->setEventType($eventName, $eventName);
            foreach ($eventMessagesData as $data) {
                $result = $eventMessage->Add($this->formatEmailMessageFields($data, $eventName));
                pre('setEventMessages: add - ' . $result);
            }
            return;
        }
        foreach ($items as $key => $item) {
            $data = $this->formatEmailMessageFields($eventMessagesData[$key], $eventName);
            $result = $eventMessage->Update($item['ID'], $data);
            if ($result) {
                pre('setEventMessages: update - ' . $eventName . ' - success.');
            } else {
                pre('setEventMessages: update - ' . $eventName . ' - save error occurred.');
            }
        }
    }

    private function formatEmailMessageFields($data, $eventName)
    {
        return [
            'EVENT_NAME' => $eventName,
            'SITE_TEMPLATE_ID' => ($data['SITE_TEMPLATE_ID']) ?: 'tstn_mail',
            'BODY_TYPE' => 'html',
            'LID' => ['s1'],
            'BCC' => $data['BCC'],
            'SUBJECT' => ($data['SUBJECT']) ?: '#SERVER_NAME#: заполнена web-форма [#RS_FORM_ID#] #RS_FORM_NAME#',
            'EMAIL_TO' => ($data['EMAIL_TO']) ?: '#DEFAULT_EMAIL_FROM#',
            'EMAIL_FROM' => ($data['EMAIL_FROM']) ?: '#DEFAULT_EMAIL_FROM#',
            'ADDITIONAL_FIELD' => ($data['ADDITIONAL_FIELD']) ?: [],
            'MESSAGE' => $data['HTML'] ?: $this->getHtmlMessage(
                $data['FIELDS'],
                $data['TEXT'],
                $data['TITLE']
            ),
        ];
    }

    public function setEventType($code, $name)
    {
        $eventType = new \CEventType();
        $res = $eventType->GetList(['EVENT_NAME' => $code]);
        $item = $res->Fetch();
        $data = [
            'LID' => 'ru',
            'EVENT_NAME' => $code,
            'NAME' => $name,
        ];
        if ($item) {
            $eventType->Update(['ID' => $item['ID']], $data);
            pre('setEventType: Update');
        } else {
            $eventType->Add($data);
            pre('setEventType: Add');
        }
    }

    private function getHtmlMessage($fields, $text, $title)
    {
        if ($text) {
            $text .= '<br><br>';
        }
        if (!$title) {
            $title = 'Заполнена web-форма: #RS_FORM_NAME#';
        }
        $fieldsString = '';
        foreach ($fields as $code => $name) {
            $fieldsString .= '<tr style="background-color:#e7e7e7">
									<td width="188">
 <span style="font-size: 12px; font-weight: bold;">' . $name . '</span>
									</td>
									<td width="326">
 <span style="font-size: 12px;">' . $code . '</span>
									</td>
								</tr>';
        }
        $htmlMessage = '<div>
	<h3 style=" margin-top: 0;">' . $title . '</h3>
	
	' . $text . '
	<table cellpadding="0" cellspacing="0" width="100%">
	<tbody>
	<tr>
		<td valign="top" align="left" style="padding: 0px; margin: 0px;">
			<table cellpadding="0" cellspacing="0" width="100%">
			<tbody>
			<tr>
				<td align="left" valign="top">
					<table width="100%" cellpadding="0" cellspacing="0" align="center">
					<tbody>
					<tr>
						<td align="left" valign="top" class="lh-5" style="color: #262626; border: 0px none transparent; font-family: Arial, Helvetica Neue, Helvetica, sans-serif; line-height: 1.55; font-size: 16px;">
							<div>
								<table width="100%">
								<tbody>
								' . $fieldsString . '
								</tbody>
								</table>
							</div>
						</td>
					</tr>
					</tbody>
					</table>
				</td>
			</tr>
			</tbody>
			</table>
		</td>
	</tr>
	</tbody>
	</table>
</div>
 <br>';

        return $htmlMessage;
    }
}