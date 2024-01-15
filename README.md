# 数据
考虑到性能和扩展问题，csv的数据导入到数据库了，目前在根目录下 Mobile_Food_Facility_Permit.sql 文件里面
修改数据库配置在： engineering-assessment-api/application/config/local.php

# nginx配置
nginx配置文件路径在根目录下 engineering.conf 文件，里面对应的域名和路径做相应修改

# 接口
/?action=list&FacilityType=Truck&pageIndex=2&size=10    查询列表接口,支持参数：
pageIndex：页码，默认1
size：数量，默认10
FacilityType：设施类型，默认空，则不筛选

/?action=info&locationid=1544284         查看单条数据，支持参数：
locationid：地点id


# Data
Considering performance and scalability issues, the CSV data has been imported into a database and is currently in the Mobile_Food_Facility_Permit.sql file at the root directory.
To modify the database configuration, go to: engineering-assessment-api/application/config/local.php

# Nginx Configuration
The nginx configuration file path is in the root directory under the file engineering.conf. You will need to make corresponding domain and path changes there.

# API
For the list query interface, use the following parameters:
/?action=list&FacilityType=Truck&pageIndex=2&size=10
pageIndex: page number, default is 1
size: number of items, default is 10
FacilityType: facility type, default is empty, in which case there will be no filtering

For single item data retrieval, use the following parameters:
/?action=info&locationid=1544284
locationid: location id