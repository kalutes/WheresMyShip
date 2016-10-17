import sys
import os

def usage():
    print "Usage: python %s [folder name]" % sys.argv[0]

def loadtargets():
    x = 0
    words = []
    fd = open("pythonfiles/target.txt", "r")
    while True:
        words.append((fd.readline())[0:-1])
        if words[x] == '':
            del(words[x])
            break
        x += 1
    return words

def keywordLookup(lines, ref):
    words = ref
    count = 0
    for n in xrange(0, len(lines)):
        org = lines[n]
        for x in xrange(0, len(words)):
            current = 0
            line = org
            for i in xrange(0, len(line)):
                current = line.find(words[x])
                if current > -1:
                    print "Found " + words[x] + "in " + line
                    count += 1
                    line = line[(current + 1):len(line)]
                else:
                    break
    return count

def main():
    if len(sys.argv) != 2:
        usage()
        return
    ref = loadtargets()
    targetdir = "./" + sys.argv[1] + "/"
    os.chdir(targetdir)
    for ls in os.listdir("./"):
        if os.path.isfile(ls):
            fd = open(ls ,'r')
            buff = []
            tempbuff = fd.readline()
            if tempbuff[len(tempbuff) - 1] == '\n':
                tempbuff = tempbuff[0:-1]
            while tempbuff != '':
                buff.append(tempbuff.lower())
                tempbuff = fd.readline()
            fd.closed
            score = keywordLookup(buff, ref)
            filename = "\"" + str(score) + "_" + ls + "\""
            complete = "\"" + ls + "\""
            os.system("mkdir -p ../priority")
            os.system("mv " + complete + " ../priority/" + filename)
    return

if __name__ == "__main__":
    main()
