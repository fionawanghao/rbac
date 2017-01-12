#基于RBAC(Role-Based Access Control)的UC系统

##UC系统简介
该系统是基于RBAC的用户权限管理系统，包含六个模块，分别是产品线(domain)管理模块、权限(resource)管理模块、角色(role)管理模块、
用户(user)管理模块、权限分配(grant)模块和提供对外接口的API模块。该系统可以通过用户角色授予以及权限与角色的对应关系来有条理
管理控制用户的访问权限，通过对外的接口可以清晰明了的查询不同用户的权限。总而言之，该系统解决了几乎所有跟权限控制有关的问题，
只需调用该接口就可以方便快捷地实现对应用使用者的权限控制和管理。



##模块介绍


###产品线(domain)管理模块
该模块包含四个小的版块，分别是添加(add)产品线、列举(list)产品线、更新(update)产品线、删除(delete)产品线。

####添加(add)产品线
功能：添加新的产品线
参数：domain_name, domain_desc, [domain_type](默认值1),[default_role_id],[status](默认值是1)
返回：0: 代表成功 1: 代表失败 msg：具体的提示信息
####列举(list)产品线
功能：按照需求将产品线记录列出
参数：start(默认值是0),limit(默认值是10)
返回：查询到结果的json串
####更新(update)产品线
功能：更新某个产品线记录的一个或多个字段
参数：id,[domain_name],[domain_desc], [domain_type],[default_role_id],[status] 
返回：0: 代表成功 1: 代表失败 msg：具体的提示信息
####删除(delete)产品线
功能：删除一个或多个产品线记录
参数：要删除的ID
返回：0: 代表成功 1: 代表失败 msg：具体的提示信息


###角色(role)管理模块
该模块包含四个小的版块，分别是添加(add)角色、列举(list)角色、更新(update)角色、删除(delete)角色。

####添加(add)角色
功能：添加新的角色
参数：domain_id, role_name, role_desc, role_type,status(默认值是1)
返回：0: 代表成功 1: 代表失败 msg：具体的提示信息
####列举(list)角色
功能：按照需求将角色记录列出
参数：start(默认值是0),limit(默认值是10)
返回：查询到结果的json串
####更新(update)角色
功能：更新某个角色记录的一个或多个字段
参数：id,[domain_id], [role_name], [role_desc], [role_type],[status] 
返回：0: 代表成功 1: 代表失败 msg：具体的提示信息
####删除(delete)角色
功能：删除一个或多个角色记录
参数：要删除的ID
返回：0: 代表成功 1: 代表失败 msg：具体的提示信息


###用户(user)管理模块
该模块包含四个小的版块，分别是添加(add)用户、列举(list)用户、更新(update)用户、删除(delete)用户。
####添加(add)用户
功能：添加新的用户
参数：[network_type](默认值是1), name, image, email,[status](默认值是1)
返回：0: 代表成功 1: 代表失败 msg：具体的提示信息
####列举(list)用户
功能：按照需求将用户记录列出
参数：start(默认值是0),limit(默认值是10)
返回：查询到结果的json串
####更新(update)用户
功能：更新某个用户记录的一个或多个字段
参数：id,[network_type],[name], [image], [email],[status]
返回：0: 代表成功 1: 代表失败 msg：具体的提示信息
####删除(delete)用户
功能：删除一个或多个用户记录
参数：要删除的ID
返回：0: 代表成功 1: 代表失败 msg：具体的提示信息


###权限(resource)管理模块
该模块包含四个小的版块，分别是添加(add)权限、列举(list)权限、更新(update)权限、删除(delete)权限。

####添加(add)权限
功能：添加新的权限
参数：domain_id, resource_url, resource_name,resource_desc,[status ] (默认值是1)
返回：0: 代表成功 1: 代表失败 msg：具体的提示信息
####列举(list)权限
功能：按照需求将权限记录列出
参数：start(默认值是0),limit(默认值是10)
返回：查询到结果的json串
####更新(update)权限
功能：更新某个权限记录的一个或多个字段
参数：id,[domain_id], [resource_url], [resource_name],[resource_desc]
返回：0: 代表成功 1: 代表失败 msg：具体的提示信息
####删除(delete)权限
功能：删除一个或多个权限记录
参数：要删除的ID
返回：0: 代表成功 1: 代表失败 msg：具体的提示信息


###权限分配(grant)模块
该模块包含四个小的版块，分别是授予用户角色(add_role)、收回用户角色(del_role)、授予角色权限(add_resource)、
收回角色权限(del_resource)。

####授予用户角色(add_role)
功能：给某个用户授予某个角色
参数：user_id, role_id
返回：0: 代表成功 1: 代表失败 msg：具体的提示信息

####收回用户角色(del_role)
功能：收回某个用户的某个角色
参数：user_id, role_id
返回：0: 代表成功 1: 代表失败 msg：具体的提示信息

####授予角色权限(add_resource)
功能：授予某个角色某项权限
参数：role_id,resource_id
返回：0: 代表成功 1: 代表失败 msg：具体的提示信息

####收回角色权限(del_resource)
功能：收回某个角色某项权限
参数：role_id,resource_id
返回：0: 代表成功 1: 代表失败 msg：具体的提示信息


###API模块
该模块包含四个小的版块，分别是查询某个产品线下的某个用户的所有权限(resources),查询某个用户在某个产品线下
所有角色(roles),查询某个用户在某个产品线下是否有某个权限(has_resource)。

####resources
功能：查询某个产品线下的某个用户的所有权限
参数：user_id，domain_id
返回：所有权限的json串

####roles
功能：查询某个用户在某个产品线下所有角色
参数：domain_id，user_id
返回：所有角色的json串

####has_resource
功能：查询某个用户在某个产品线下是否有某个权限
参数：user_id,domain_id,resource_url
返回：0: 代表有 1: 代表没有 msg：具体的提示信息

















