cmake_minimum_required(VERSION 2.4)
set(CMAKE_ALLOW_LOOSE_LOOP_CONSTRUCTS true)
set(CMAKE_BUILD_TYPE release)
include_directories(. ../misc)
include(CheckIncludeFileCXX)
check_include_file_cxx(sys/epoll.h HAVE_SYS_EPOLL)
if(HAVE_SYS_EPOLL)
       add_definitions(-DEPOLL)
endif()
add_executable(
	xbt_tracker
	../misc/sql/database.cpp
	../misc/sql/sql_query.cpp
	../misc/sql/sql_result.cpp
	../misc/bt_misc.cpp
	../misc/bvalue.cpp
	../misc/sha1.cpp
	../misc/socket.cpp
	../misc/virtual_binary.cpp
	../misc/xcc_z.cpp
	config.cpp
	connection.cpp
	epoll.cpp
	server.cpp
	tcp_listen_socket.cpp
	tracker_input.cpp
	transaction.cpp
	udp_listen_socket.cpp
	"XBT Tracker.cpp"
)
target_link_libraries(xbt_tracker mysqlclient)
