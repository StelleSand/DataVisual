# DataVisual
实时用电订单可视化网站项目
为实现计划任务——每分钟扫描以提交order/power,在linux环境下请添加crontab任务，执行"crontab -e"在配置文件末尾添加"* * * * * php /var/www/DataVisual/artisan schedule:run >> /var/www/log/web.txt 2>&1"
