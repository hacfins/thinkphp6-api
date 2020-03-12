# javaphp_demo

#### 介绍
php通过javabridge调java方法的demo

#### 软件架构
软件架构说明


#### 安装教程

1. 安装好jdk（javabridge是要求1.4或更高，我机器是直接装的jdk8，可以通过命令行窗口输入java -version 确保java已安装且是正确的版本）
2. 安装好php环境（我机器装的php7.13，可以通过php -version查看信息）
3. 去http://php-java-bridge.sourceforge.net/pjb/download.php下载 JavaBridge.jar，Java.inc两个文件即可（不用下载JavaBridge.war去tomcat解压）；
4. 对JavaBridge.jar解压（我常用7z，rar没试过），修改解压出来的META-INF/MANIFEST.MF，在Class-Path: log4j.jar后加入依赖的包，比如我需要加lib/httpclient.jar，最后是这样的
- Class-Path: log4j.jar lib/httpclient-4.1.3.jar
- (注意，依赖的jar包，多个jar之间是用空格分隔，还需要注意引用的路径是相对路径）
5. 将修改的好的文件重新打包成jar
- 先命令行cd到jar的解压包目录
- 执行：jar -cMf JavaBridge.jar .   （意思就是把当前目录下的文件都打进JavaBridge.jar，并且不生成新的清单文件）
- 运行javabridge 

#### 使用说明

1. xxxx
2. xxxx
3. xxxx
