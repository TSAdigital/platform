<?php

namespace app\widgets;

use Yii;
use yii\base\Widget;
use yii\helpers\Html;

class SidebarMenu extends Widget
{
    public $items = [];
    public $userPermissions = [];
    private $defaultIcon = 'minus';

    public function init()
    {
        parent::init();

        if (empty($this->userPermissions)) {
            $permissions = Yii::$app->authManager->getPermissionsByUser(Yii::$app->user->id);
            $this->userPermissions = array_keys($permissions);
        }
    }

    public function run()
    {
        $output = '<ul class="sidebar-nav">';

        foreach ($this->items as $item) {
            if (!$this->isVisible($item)) {
                continue;
            }

            if ($this->isHeader($item)) {
                $output .= $this->renderHeader($item);
            } else {
                $output .= $this->renderItem($item);
            }
        }

        $output .= '</ul>';

        return $output;
    }

    private function isHeader($item)
    {
        return isset($item['header']);
    }

    private function renderHeader($item)
    {
        return Html::tag('li', htmlspecialchars($item['header']), ['class' => 'sidebar-header']);
    }

    private function isVisible($item)
    {
        if (!isset($item['visible'])) {
            return true;
        }

        if (is_bool($item['visible'])) {
            return $item['visible'];
        }

        if (is_string($item['visible'])) {
            return in_array($item['visible'], $this->userPermissions);
        }

        if (is_array($item['visible'])) {
            foreach ($item['visible'] as $permission) {
                if (in_array($permission, $this->userPermissions)) {
                    return true;
                }
            }
            return false;
        }

        return false;
    }

    private function renderItem($item)
    {
        $label = htmlspecialchars($item['label'] ?? 'Untitled');
        $url = $item['url'] ?? '#';
        $iconName = $this->getIconName($item);
        $icon = Html::tag('svg', '', ['class' => 'align-middle', 'data-feather' => $iconName]);
        $activeClass = $this->isActive($item) ? 'active' : '';

        return Html::tag('li', Html::a(
            $icon . ' <span class="align-middle">' . $label . '</span>',
            $url,
            ['class' => 'sidebar-link ' . $activeClass]
        ), ['class' => 'sidebar-item ' . $activeClass]);
    }

    private function getIconName($item)
    {
        return isset($item['icon']) && in_array($item['icon'], $this->getAvailableIcons())
            ? $item['icon']
            : $this->defaultIcon;
    }

    private function isActive($item)
    {
        return isset($item['active']) && $item['active'];
    }

    private function getAvailableIcons()
    {
        return [
            'activity', 'airplay', 'alert-circle', 'alert-octagon', 'alert-triangle',
            'align-center', 'align-justify', 'align-left', 'align-right', 'anchor',
            'aperture', 'arrow-down', 'arrow-down-circle', 'arrow-down-left',
            'arrow-down-right', 'arrow-left', 'arrow-left-circle', 'arrow-right',
            'arrow-right-circle', 'arrow-up', 'arrow-up-circle', 'arrow-up-left',
            'arrow-up-right', 'at-sign', 'award', 'bar-chart', 'bar-chart-2',
            'battery', 'battery-charging', 'bell', 'bell-off', 'bluetooth',
            'bold', 'book', 'book-open', 'bookmark', 'box', 'briefcase', 'calendar',
            'camera', 'camera-off', 'cast', 'check', 'check-circle', 'check-square',
            'chevron-down', 'chevron-left', 'chevron-right', 'chevron-up',
            'chevrons-down', 'chevrons-left', 'chevrons-right', 'chevrons-up',
            'chrome', 'circle', 'clipboard', 'clock', 'cloud', 'cloud-drizzle',
            'cloud-lightning', 'cloud-off', 'cloud-rain', 'cloud-snow', 'code',
            'codepen', 'command', 'compass', 'copy', 'corner-down-left',
            'corner-down-right', 'corner-left-down', 'corner-left-up',
            'corner-right-down', 'corner-right-up', 'corner-up-left',
            'corner-up-right', 'cpu', 'credit-card', 'crop', 'crosshair',
            'database', 'delete', 'disc', 'dollar-sign', 'download', 'download-cloud',
            'droplet', 'edit', 'edit-2', 'edit-3', 'external-link', 'eye',
            'eye-off', 'facebook', 'fast-forward', 'feather', 'file', 'file-minus',
            'file-plus', 'file-text', 'film', 'filter', 'flag', 'folder',
            'folder-minus', 'folder-plus', 'git-branch', 'git-commit',
            'git-merge', 'git-pull-request', 'github', 'gitlab', 'globe',
            'grid', 'hard-drive', 'hash', 'headphones', 'heart', 'help-circle',
            'home', 'image', 'inbox', 'info', 'instagram', 'italic', 'layers',
            'layout', 'link', 'link-2', 'linkedin', 'list', 'loader', 'lock',
            'log-in', 'log-out', 'mail', 'map', 'map-pin', 'maximize',
            'maximize-2', 'menu', 'message-circle', 'message-square', 'mic','mic-off',
            'minimize', 'minimize-2', 'minus', 'minus-circle',
            'minus-square', 'monitor', 'moon', 'more-horizontal', 'more-vertical',
            'move', 'music', 'navigation', 'navigation-2', 'octagon', 'package',
            'paperclip', 'pause', 'pause-circle', 'percent', 'phone', 'phone-call',
            'phone-forwarded', 'phone-incoming', 'phone-missed', 'phone-off',
            'phone-outgoing', 'pie-chart', 'play', 'play-circle', 'plus',
            'plus-circle', 'plus-square', 'pocket', 'power', 'printer', 'radio',
            'refresh-ccw', 'refresh-cw', 'repeat', 'rewind', 'rotate-ccw',
            'rotate-cw', 'rss', 'save', 'scissors', 'search', 'send',
            'server', 'settings', 'share', 'share-2', 'shield', 'shield-off',
            'shopping-bag', 'shopping-cart', 'shuffle', 'sidebar', 'skip-back',
            'skip-forward', 'slack', 'slash', 'sliders', 'smartphone',
            'speaker', 'square', 'star', 'stop-circle', 'sun', 'sunrise',
            'sunset', 'tablet', 'tag', 'target', 'terminal', 'thermometer',
            'thumbs-down', 'thumbs-up', 'toggle-left', 'toggle-right',
            'trash', 'trash-2', 'trending-down', 'trending-up', 'triangle',
            'truck', 'tv', 'twitter', 'type', 'umbrella', 'underline',
            'unlock', 'upload', 'upload-cloud', 'user', 'user-check',
            'user-minus', 'user-plus', 'user-x', 'users', 'video',
            'video-off', 'voicemail', 'volume', 'volume-1', 'volume-2',
            'volume-x', 'watch', 'wifi', 'wifi-off', 'wind', 'x', 'x-circle',
            'x-square', 'zap', 'zap-off', 'zoom-in', 'zoom-out'
        ];
    }
}